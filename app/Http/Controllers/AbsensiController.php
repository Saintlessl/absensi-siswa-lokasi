<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\LokasiSekolah;
use App\Models\OrangTua;
use App\Models\Room;
use App\Models\Siswa;
use App\Services\FaceBiometricService;
use App\Services\WhatsAppNotificationService;
use App\Services\RoomAssignmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AbsensiController extends Controller
{
    protected FaceBiometricService $biometricService;
    protected WhatsAppNotificationService $whatsAppService;

    public function __construct(
        FaceBiometricService $biometricService,
        WhatsAppNotificationService $whatsAppService
    ) {
        $this->biometricService = $biometricService;
        $this->whatsAppService = $whatsAppService;
    }

    public function index()
    {
        $user = Auth::user();
        $siswa = null;

        if ($user->role == 'orangtua') {
            $dataOrangTua = OrangTua::where('user_id', $user->id)->first();
            if ($dataOrangTua) {
                $siswa = $dataOrangTua->siswa()->with(['kelas.jurusan', 'faceBiometric'])->first();
            }
        } else {
            $siswa = Siswa::with(['kelas.jurusan', 'faceBiometric'])->where('user_id', $user->id)->first();
        }

        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }

        $lokasiSekolah = LokasiSekolah::first();
        if (!$lokasiSekolah) {
            return redirect()->route('dashboard')->with('error', 'Koordinat Lokasi Sekolah belum diatur oleh Admin.');
        }

        $absenHariIni = Absensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', date('Y-m-d'))
            ->first();

        $isFaceRegistered = $siswa->isFaceRegistered();

        return view('absensi.index', compact('siswa', 'lokasiSekolah', 'absenHariIni', 'isFaceRegistered'));
    }

    /**
     * Issue an anti-replay challenge nonce for attendance face verification.
     * The descriptor returned by the client is matched against the stored
     * template at AbsensiController::store().
     */
    public function attendanceChallenge()
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Identitas siswa tidak ditemukan.'
            ], 403);
        }

        $challenge = $this->biometricService->generateAttendanceChallenge($siswa);

        return response()->json([
            'success'         => true,
            'challenge_token' => $challenge['token'],
            'challenges'      => $challenge['challenges'],
            'expires_in'      => $challenge['expires_in'],
        ]);
    }

    public function validateQr(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|string',
        ]);

        $user = Auth::user();
        $siswa = Siswa::with('kelas.jurusan')->where('user_id', $user->id)->first();
        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan.'], 403);
        }

        $assignment = app(RoomAssignmentService::class)->activeForToken($request->qr_token, Carbon::now());
        $kelas = $assignment?->kelas ?? Kelas::with('jurusan')->where('qr_token', $request->qr_token)->first();
        if (!$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'QR ruangan tidak valid, nonaktif, atau tidak memiliki alokasi aktif.'
            ], 404);
        }

        if ($siswa->kelas_id != $kelas->id) {
            $namaKelasSiswa = $siswa->kelas->nama_kelas ?? 'Kelas Terdaftar Anda';
            return response()->json([
                'success' => false,
                'message' => "QR Code ini untuk {$kelas->nama_kelas}, sedangkan Anda terdaftar di {$namaKelasSiswa}!",
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "QR " . ($assignment ? "ruangan {$assignment->room->name}" : 'kelas') . " valid untuk {$kelas->nama_kelas}.",
            'kelas' => [
                'id' => $kelas->id,
                'nama_kelas' => $kelas->nama_kelas,
                'jurusan' => $kelas->jurusan->nama_jurusan ?? '-',
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'qr_token'          => 'required|string',
            'foto'              => 'required|string',
            'latitude'          => 'required',
            'longitude'         => 'required',
            'tipe'              => 'nullable|in:masuk,pulang',
            'face_descriptor'   => 'required|array|size:128',
            'face_descriptor.*' => 'numeric',
            'challenge_token'   => 'required|string',
            'liveness_proof'    => 'required|array',
            'liveness_proof.completed_challenges' => 'required|array|min:1',
        ]);

        $user = Auth::user();
        $siswa = Siswa::with(['kelas.jurusan', 'faceBiometric', 'orangtua'])->where('user_id', $user->id)->first();

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Anda tidak terdaftar sebagai siswa.'], 403);
        }

        $tipe = $request->tipe ?? 'masuk';

        // 1. Validasi QR Code token
        $assignment = app(RoomAssignmentService::class)->activeForToken($request->qr_token, Carbon::now());
        $kelas = $assignment?->kelas ?? Kelas::where('qr_token', $request->qr_token)->first();
        if (!$kelas) {
            return response()->json(['success' => false, 'message' => 'QR ruangan tidak valid, nonaktif, atau tidak memiliki alokasi aktif.'], 404);
        }

        if ($siswa->kelas_id != $kelas->id) {
            $namaKelasSiswa = $siswa->kelas->nama_kelas ?? 'Kelas Anda';
            return response()->json([
                'success' => false,
                'message' => "QR Code ini milik {$kelas->nama_kelas}, bukan {$namaKelasSiswa}!",
            ], 422);
        }

        // 2. Validasi Geofencing Lokasi Sekolah
        $lokasiSekolah = LokasiSekolah::first();
        if (!$lokasiSekolah) {
            return response()->json(['success' => false, 'message' => 'Titik lokasi sekolah belum diatur oleh admin.'], 500);
        }

        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;

        $earthRadius = 6371000; // meter
        $latFrom = deg2rad($lokasiSekolah->latitude);
        $lonFrom = deg2rad($lokasiSekolah->longitude);
        $latTo = deg2rad($lat);
        $lonTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        $distance = $angle * $earthRadius;

        if ($distance > $lokasiSekolah->radius) {
            return response()->json([
                'success' => false,
                'message' => 'Anda berada di luar radius sekolah (' . round($distance) . ' m dari sekolah, batas: ' . $lokasiSekolah->radius . ' m).'
            ], 400);
        }

        // 3. Verifikasi Biometrik WAJIB — anti titip absen.
        //    Tanpa verifikasi wajah yang cocok, presensi ditolak.
        $verification = $this->biometricService->verify(
            $siswa,
            $request->face_descriptor,
            $request->challenge_token,
            $request->liveness_proof ?? [],
            $request->ip(),
            $request->userAgent(),
            $tipe
        );

        if (!$verification['success']) {
            return response()->json([
                'success' => false,
                'message' => $verification['message'] ?? 'Verifikasi wajah gagal. Presensi tidak dicatat.',
            ], 422);
        }

        $confidence = (float) ($verification['confidence'] ?? 0.0);
        $livenessScore = (float) ($verification['liveness_score'] ?? 0.0);
        $isBiometricVerified = true;

        // 4. Simpan Foto Selfie Base64
        $fotoData = $request->foto;
        if (preg_match('/^data:image\/(\w+);base64,/', $fotoData, $type)) {
            $fotoData = substr($fotoData, strpos($fotoData, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, jpeg
            $fotoData = base64_decode($fotoData);

            if ($fotoData === false) {
                return response()->json(['success' => false, 'message' => 'Gagal memproses foto selfie.'], 400);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Format foto selfie tidak valid.'], 400);
        }

        $now = Carbon::now();
        $todayStr = $now->format('Y-m-d');

        $absenHariIni = Absensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', $todayStr)
            ->first();

        // 5. Proses Berdasarkan Tipe Absen (Waktu Server)
        if ($tipe === 'pulang') {
            // Syarat Pulang:
            // - Harus sudah absen masuk
            if (!$absenHariIni) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum melakukan absen masuk hari ini!'
                ], 400);
            }

            // - Belum absen pulang
            if ($absenHariIni->jam_pulang) {
                return response()->json([
                    'success' => false,
                    'message' => "Anda sudah melakukan absen pulang hari ini pukul {$absenHariIni->jam_pulang}."
                ], 400);
            }

            // - Waktu pulang minimal 14:55 WIB (5 menit sebelum jam 15:00)
            $batasPulang = Carbon::today()->setTime(14, 55, 0);
            if ($now->lt($batasPulang)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum waktunya absen pulang. Absen pulang dibuka mulai pukul 14:55 WIB (sekarang ' . $now->format('H:i') . ' WIB).'
                ], 400);
            }

            $fileName = 'selfie_pulang_' . $siswa->id . '_' . $now->format('Ymd_His') . '.' . ($type ?? 'jpg');
            Storage::disk('public')->put('selfie/' . $fileName, $fotoData);

            $absenHariIni->update([
                'jam_pulang'            => $now->format('H:i:s'),
                'room_id'               => $assignment?->room_id ?? $absenHariIni->room_id,
                'foto_pulang'           => 'selfie/' . $fileName,
                'confidence_pulang'     => $confidence,
                'liveness_score_pulang' => $livenessScore,
                'latitude_pulang'       => $lat,
                'longitude_pulang'      => $lng,
            ]);

            // Kirim Notifikasi WhatsApp Otomatis ke Orang Tua
            $waResult = $this->whatsAppService->sendAttendanceNotification($siswa, $absenHariIni, 'pulang');

            $waNotice = $waResult['success'] ? ' Notifikasi WhatsApp otomatis telah dikirim ke orang tua.' : '';

            return response()->json([
                'success' => true,
                'message' => "Absen pulang berhasil dicatat pukul " . $now->format('H:i') . " WIB.{$waNotice} Selamat beristirahat!",
                'data' => [
                    'tipe' => 'pulang',
                    'jam' => $now->format('H:i'),
                    'confidence' => $confidence,
                    'whatsapp' => $waResult
                ]
            ]);

        } else {
            // Tipe: MASUK
            if ($absenHariIni) {
                return response()->json([
                    'success' => false,
                    'message' => "Anda sudah melakukan absen masuk hari ini pukul {$absenHariIni->jam_masuk}."
                ], 400);
            }

            $batasMasuk = Carbon::today()->setTime(7, 15, 0);
            $keteranganTerlambat = null;
            $menitTerlambat = 0;
            $keteranganStatus = 'Tepat Waktu';

            if ($now->gt($batasMasuk)) {
                $menitTerlambat = $batasMasuk->diffInMinutes($now);
                $jam = intdiv($menitTerlambat, 60);
                $sisaMenit = $menitTerlambat % 60;

                if ($jam > 0 && $sisaMenit > 0) {
                    $keteranganTerlambat = "Terlambat {$jam} jam {$sisaMenit} menit";
                } elseif ($jam > 0) {
                    $keteranganTerlambat = "Terlambat {$jam} jam";
                } else {
                    $keteranganTerlambat = "Terlambat {$sisaMenit} menit";
                }
                $keteranganStatus = $keteranganTerlambat;
            }

            $fileName = 'selfie_masuk_' . $siswa->id . '_' . $now->format('Ymd_His') . '.' . ($type ?? 'jpg');
            Storage::disk('public')->put('selfie/' . $fileName, $fotoData);

            $absensi = Absensi::create([
                'siswa_id'              => $siswa->id,
                'room_id'               => $assignment?->room_id,
                'tanggal'               => $todayStr,
                'jam_masuk'             => $now->format('H:i:s'),
                'status'                => 'Hadir',
                'keterangan'            => $keteranganStatus,
                'keterangan_terlambat'  => $keteranganTerlambat,
                'menit_terlambat'       => $menitTerlambat,
                'foto_masuk'            => 'selfie/' . $fileName,
                'confidence_masuk'      => $confidence,
                'liveness_score_masuk'  => $livenessScore,
                'is_verified_biometric' => $isBiometricVerified,
                'latitude'              => $lat,
                'longitude'             => $lng,
            ]);

            // Kirim Notifikasi WhatsApp Otomatis ke Orang Tua
            $waResult = $this->whatsAppService->sendAttendanceNotification($siswa, $absensi, 'masuk');
            $waNotice = $waResult['success'] ? ' Notifikasi WhatsApp otomatis telah dikirim ke orang tua.' : '';

            $pesanSukses = $keteranganTerlambat
                ? "Absen masuk berhasil dicatat pukul {$now->format('H:i')} WIB ({$keteranganTerlambat}).{$waNotice}"
                : "Absen masuk berhasil tepat waktu pukul {$now->format('H:i')} WIB!{$waNotice}";

            return response()->json([
                'success' => true,
                'message' => $pesanSukses,
                'data' => [
                    'tipe' => 'masuk',
                    'jam' => $now->format('H:i'),
                    'confidence' => $confidence,
                    'terlambat' => $keteranganTerlambat,
                    'whatsapp' => $waResult
                ]
            ]);
        }
    }

    public function qrCodes(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin', 'kaprog'])) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki hak akses untuk halaman QR Code Ruangan.');
        }

        $query = Room::with(['assignments' => function ($q) {
            $today = Carbon::today()->toDateString();
            $q->where(function ($sub) use ($today) {
                $sub->whereNull('starts_on')->orWhere('starts_on', '<=', $today);
            })->where(function ($sub) use ($today) {
                $sub->whereNull('ends_on')->orWhere('ends_on', '>=', $today);
            })->with('kelas.jurusan');
        }]);

        if ($request->filled('building')) {
            $query->where('building', $request->building);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('code', 'LIKE', '%' . $search . '%')
                    ->orWhere('building', 'LIKE', '%' . $search . '%');
            });
        }

        $rooms = $query->orderBy('name')->get();
        $buildings = Room::whereNotNull('building')->distinct()->pluck('building');
        $types = Room::whereNotNull('type')->distinct()->pluck('type');

        return view('absensi.qr-codes', [
            'rooms'     => $rooms,
            'buildings' => $buildings,
            'types'     => $types,
            'building'  => $request->building ?? '',
            'type'      => $request->type ?? '',
            'search'    => $request->search ?? '',
        ]);
    }

    public function riwayatKehadiran()
    {
        $user = Auth::user();
        $siswa = null;

        if ($user->role == 'orangtua') {
            $dataOrangTua = OrangTua::where('user_id', $user->id)->first();
            if ($dataOrangTua) {
                $siswa = $dataOrangTua->siswa()->first();
            }
        } else {
            $siswa = Siswa::where('user_id', $user->id)->first();
        }

        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }

        $riwayat = Absensi::where('siswa_id', $siswa->id)
            ->orderBy('tanggal', 'desc')
            ->get();

        $bulanIni = date('m');
        $tahunIni = date('Y');

        $baseQuery = Absensi::where('siswa_id', $siswa->id)
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni);

        $hadirCount = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['hadir'])->count();
        $izinCount  = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['izin'])->count();
        $sakitCount = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['sakit'])->count();
        $alpaCount  = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['alfa'])->count();

        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now();
        $hariEfektif = 0;

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            if ($currentDate->dayOfWeek !== Carbon::SUNDAY) {
                $hariEfektif++;
            }
            $currentDate->addDay();
        }

        $totalTercatat = $hadirCount + $izinCount + $sakitCount + $alpaCount;
        $calculatedAlpa = max(0, $hariEfektif - $totalTercatat);

        $rekap = [
            'hadir' => $hadirCount,
            'izin'  => $izinCount,
            'sakit' => $sakitCount,
            'alpa'  => $alpaCount + $calculatedAlpa,
        ];

        return view('absensi.riwayat-kehadiran', compact('riwayat', 'rekap', 'siswa'));
    }

    public function absensiHarian()
    {
        $user = Auth::user();
        if ($user->role !== 'guru') {
            return redirect()->route('dashboard')->with('error', 'Hanya guru yang dapat mengakses halaman ini.');
        }

        $guru = Guru::where('user_id', $user->id)->first();
        if (!$guru) {
            return redirect()->route('dashboard')->with('error', 'Data guru tidak ditemukan.');
        }

        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        $hariIniStr = $days[date('l')];

        $jadwalHariIni = Jadwal::where('guru_id', $guru->id)
            ->where('hari', $hariIniStr)
            ->with('kelas')
            ->get();

        $kelasIds = $jadwalHariIni->pluck('kelas_id')->unique();

        $siswas = Siswa::whereIn('kelas_id', $kelasIds)
            ->with(['kelas', 'absensi' => function ($q) {
                $q->whereDate('tanggal', date('Y-m-d'));
            }])
            ->orderBy('nama')
            ->paginate(10);

        return view('absensi.harian', compact('guru', 'jadwalHariIni', 'siswas', 'hariIniStr'));
    }

    public function updateHarian(Request $request)
    {
        $request->validate([
            'siswa_id'   => 'required|exists:siswas,id',
            'status'     => 'required|in:Hadir,Izin,Sakit,Alfa',
            'keterangan' => 'nullable|string'
        ]);

        $user = Auth::user();

        // Non-superadmin must be scoped to the classes they are allowed to manage:
        //  - guru  : classes taught (Jadwal) + homeroom class (activeHomeroomAssignment)
        //  - kaprog: additionally every class inside their own jurusan
        //  - admin : unrestricted within the school (same as superadmin below)
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            $guru = Guru::with(['activeHomeroomAssignment', 'activeSubjectAssignments'])
                ->where('user_id', $user->id)
                ->first();

            $allowedClassIds = collect();

            if ($guru) {
                $allowedClassIds = $guru->activeSubjectAssignments->pluck('kelas_id')
                    ->merge($guru->activeJadwal()->pluck('kelas_id'))
                    ->merge($guru->activeHomeroomAssignment ? collect([$guru->activeHomeroomAssignment->kelas_id]) : collect())
                    ->unique();
            }

            // Kaprog: allowed to manage every class inside their jurusan.
            if ($user->role === 'kaprog' && $user->jurusan_id) {
                $allowedClassIds = $allowedClassIds->merge(
                    Kelas::where('jurusan_id', $user->jurusan_id)->pluck('id')
                )->unique();
            }

            $siswa = Siswa::find($request->siswa_id);

            if (!$siswa || !$siswa->kelas_id || !$allowedClassIds->contains($siswa->kelas_id)) {
                return redirect()->back()->with('error', 'Akses ditolak: Anda tidak memiliki wewenang untuk mengubah absensi siswa ini.');
            }
        }

        $tanggalInfo = date('Y-m-d');

        $absensi = Absensi::where('siswa_id', $request->siswa_id)
            ->whereDate('tanggal', $tanggalInfo)
            ->first();

        if ($absensi) {
            $absensi->update([
                'status'     => $request->status,
                'keterangan' => $request->keterangan ?? 'Diperbarui otomatis oleh Guru'
            ]);
        } else {
            Absensi::create([
                'siswa_id'   => $request->siswa_id,
                'tanggal'    => $tanggalInfo,
                'jam_masuk'  => date('H:i:s'),
                'status'     => $request->status,
                'keterangan' => $request->keterangan ?? 'Ditambahkan otomatis oleh Guru',
            ]);
        }

        return redirect()->back()->with('success', 'Status absensi berhasil diperbarui.');
    }

    public function rekapAbsensi(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $siswas = Siswa::with('kelas')
            ->orderByDesc(
                Absensi::selectRaw('MAX(tanggal)')
                    ->whereColumn('siswa_id', 'siswas.id')
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
            )
            ->orderBy('nama')
            ->get();

        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $endDate = ($bulan == date('m') && $tahun == date('Y'))
            ? Carbon::now()
            : Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();

        $hariEfektif = 0;
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            if ($currentDate->dayOfWeek !== Carbon::SUNDAY) {
                $hariEfektif++;
            }
            $currentDate->addDay();
        }

        $rekapAll = [];
        foreach ($siswas as $s) {
            $hadir = Absensi::where('siswa_id', $s->id)
                ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                ->whereRaw('LOWER(status) = ?', ['hadir'])->count();
            $izin = Absensi::where('siswa_id', $s->id)
                ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                ->whereRaw('LOWER(status) = ?', ['izin'])->count();
            $sakit = Absensi::where('siswa_id', $s->id)
                ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                ->whereRaw('LOWER(status) = ?', ['sakit'])->count();
            $alfaDb = Absensi::where('siswa_id', $s->id)
                ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                ->whereRaw('LOWER(status) = ?', ['alfa'])->count();

            $totalTercatat = $hadir + $izin + $sakit + $alfaDb;
            $alpa = $alfaDb + max(0, $hariEfektif - $totalTercatat);

            $rekapAll[] = [
                'siswa' => $s,
                'hadir' => $hadir,
                'izin'  => $izin,
                'sakit' => $sakit,
                'alpa'  => $alpa,
            ];
        }

        $page = $request->get('page', 1);
        $perPage = 10;
        $totalSiswa = count($rekapAll);
        $rekap = new LengthAwarePaginator(
            array_slice($rekapAll, ($page - 1) * $perPage, $perPage),
            $totalSiswa,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->isoFormat('MMMM Y');

        return view('absensi.rekap-absensi', compact('rekap', 'bulan', 'tahun', 'namaBulan', 'hariEfektif', 'totalSiswa'));
    }

    public function rekapPerKelas(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $kelasList = Kelas::withCount('siswa')->orderBy('nama_kelas')->get();

        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $endDate = ($bulan == date('m') && $tahun == date('Y'))
            ? Carbon::now()
            : Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();

        $tanggalList = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            if ($currentDate->dayOfWeek !== Carbon::SUNDAY) {
                $tanggalList[] = $currentDate->format('Y-m-d');
            }
            $currentDate->addDay();
        }

        $tanggalList = array_reverse($tanggalList);

        $rekapAll = [];
        foreach ($tanggalList as $tgl) {
            foreach ($kelasList as $kelas) {
                $siswaIds = Siswa::where('kelas_id', $kelas->id)->pluck('id');
                $totalSiswa = $siswaIds->count();

                $hadir = Absensi::whereIn('siswa_id', $siswaIds)
                    ->whereDate('tanggal', $tgl)
                    ->whereRaw('LOWER(status) = ?', ['hadir'])->count();
                $izin = Absensi::whereIn('siswa_id', $siswaIds)
                    ->whereDate('tanggal', $tgl)
                    ->whereRaw('LOWER(status) = ?', ['izin'])->count();
                $sakit = Absensi::whereIn('siswa_id', $siswaIds)
                    ->whereDate('tanggal', $tgl)
                    ->whereRaw('LOWER(status) = ?', ['sakit'])->count();
                $alfaDb = Absensi::whereIn('siswa_id', $siswaIds)
                    ->whereDate('tanggal', $tgl)
                    ->whereRaw('LOWER(status) = ?', ['alfa'])->count();

                $tercatat = $hadir + $izin + $sakit + $alfaDb;
                $alpa = $alfaDb + max(0, $totalSiswa - $tercatat);

                $rekapAll[] = [
                    'tanggal'     => $tgl,
                    'kelas'       => $kelas->nama_kelas,
                    'total_siswa' => $totalSiswa,
                    'hadir'       => $hadir,
                    'izin'        => $izin,
                    'sakit'       => $sakit,
                    'alpa'        => $alpa,
                ];
            }
        }

        $page = $request->get('page', 1);
        $perPage = 10;
        $totalBaris = count($rekapAll);
        $rekap = new LengthAwarePaginator(
            array_slice($rekapAll, ($page - 1) * $perPage, $perPage),
            $totalBaris,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->isoFormat('MMMM Y');

        return view('absensi.rekap-kelas', compact('rekap', 'bulan', 'tahun', 'namaBulan', 'totalBaris'));
    }
}
