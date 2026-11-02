<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\LokasiSekolah;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Services\FaceBiometricService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AbsensiController extends Controller
{
    protected FaceBiometricService $biometricService;

    public function __construct(FaceBiometricService $biometricService)
    {
        $this->biometricService = $biometricService;
    }

    public function index()
    {
        $user = Auth::user();
        $siswa = null;

        if ($user->role == 'orangtua') {
            $dataOrangTua = OrangTua::where('user_id', $user->id)->first();
            if ($dataOrangTua) {
                $siswa = Siswa::with(['kelas.jurusan', 'faceBiometric'])->find($dataOrangTua->siswa_id);
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

        $kelas = Kelas::with('jurusan')->where('qr_token', $request->qr_token)->first();
        if (!$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau tidak terdaftar di sistem sekolah!'
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
            'message' => "QR Code valid untuk {$kelas->nama_kelas}.",
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
        ]);

        $user = Auth::user();
        $siswa = Siswa::with(['kelas', 'faceBiometric'])->where('user_id', $user->id)->first();

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Anda tidak terdaftar sebagai siswa.'], 403);
        }

        $tipe = $request->tipe ?? 'masuk';

        // 1. Validasi QR Code token
        $kelas = Kelas::where('qr_token', $request->qr_token)->first();
        if (!$kelas) {
            return response()->json(['success' => false, 'message' => 'QR Code kelas tidak valid.'], 404);
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

        // 3. AI Facial Verification & Anti-Spoofing (Liveness Check)
        $verification = $this->biometricService->verify(
            $siswa,
            $request->face_descriptor,
            $request->challenge_token,
            $request->liveness_proof,
            $request->ip(),
            $request->userAgent(),
            $tipe
        );

        if (!$verification['success']) {
            return response()->json([
                'success'    => false,
                'status'     => $verification['status'],
                'message'    => $verification['message'],
                'confidence' => $verification['confidence'] ?? null,
                'distance'   => $verification['distance'] ?? null
            ], 422);
        }

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
                'foto_pulang'           => 'selfie/' . $fileName,
                'confidence_pulang'     => $verification['confidence'],
                'liveness_score_pulang' => $verification['liveness_score'],
                'latitude_pulang'       => $lat,
                'longitude_pulang'      => $lng,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Absen pulang berhasil diverifikasi biometrik ({$verification['confidence']}%) pukul " . $now->format('H:i') . ' WIB. Selamat beristirahat!',
                'data' => [
                    'tipe' => 'pulang',
                    'jam' => $now->format('H:i'),
                    'confidence' => $verification['confidence']
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
                'tanggal'               => $todayStr,
                'jam_masuk'             => $now->format('H:i:s'),
                'status'                => 'Hadir',
                'keterangan'            => $keteranganStatus,
                'keterangan_terlambat'  => $keteranganTerlambat,
                'menit_terlambat'       => $menitTerlambat,
                'foto_masuk'            => 'selfie/' . $fileName,
                'confidence_masuk'      => $verification['confidence'],
                'liveness_score_masuk'  => $verification['liveness_score'],
                'is_verified_biometric' => true,
                'latitude'              => $lat,
                'longitude'             => $lng,
            ]);

            $pesanSukses = $keteranganTerlambat
                ? "Absen masuk berhasil diverifikasi biometrik ({$verification['confidence']}%) pukul {$now->format('H:i')} WIB ({$keteranganTerlambat})."
                : "Absen masuk berhasil! Wajah terverifikasi biometrik ({$verification['confidence']}%) tepat waktu pukul {$now->format('H:i')} WIB.";

            return response()->json([
                'success' => true,
                'message' => $pesanSukses,
                'data' => [
                    'tipe' => 'masuk',
                    'jam' => $now->format('H:i'),
                    'confidence' => $verification['confidence'],
                    'terlambat' => $keteranganTerlambat,
                ]
            ]);
        }
    }

    public function qrCodes(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return redirect()->route('dashboard')->with('error', 'Hanya Superadmin atau Admin yang dapat mengakses halaman QR Code.');
        }

        $query = Kelas::with(['jurusan', 'siswa']);

        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nama_kelas', 'LIKE', '%' . $search . '%');
        }

        $kelasList = $query->orderBy('nama_kelas')->get();
        $jurusans = Jurusan::orderBy('kode_jurusan')->get();

        return view('absensi.qr-codes', compact('kelasList', 'jurusans') + [
            'jurusan_id' => $request->jurusan_id ?? '',
            'search'     => $request->search ?? ''
        ]);
    }

    public function riwayatKehadiran()
    {
        $user = Auth::user();
        $siswa = null;

        if ($user->role == 'orangtua') {
            $dataOrangTua = OrangTua::where('user_id', $user->id)->first();
            if ($dataOrangTua) {
                $siswa = Siswa::find($dataOrangTua->siswa_id);
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
