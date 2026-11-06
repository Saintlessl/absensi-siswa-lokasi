<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\LokasiSekolah;
use App\Models\OrangTua;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class AbsensiController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $siswa = null;

        if ($user->role == 'orangtua') {
            $dataOrangTua = OrangTua::where('user_id', $user->id)->first();
            if ($dataOrangTua) {
                $siswa = Siswa::with('kelas')->find($dataOrangTua->siswa_id);
            }
        } else {
            $siswa = Siswa::with('kelas')->where('user_id', $user->id)->first();
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

        return view('absensi.index', compact('siswa', 'lokasiSekolah', 'absenHariIni'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Anda bukan siswa'], 403);
        }

        $lokasiSekolah = LokasiSekolah::first();
        if (!$lokasiSekolah) {
            return response()->json(['success' => false, 'message' => 'Lokasi sekolah belum diatur'], 500);
        }

        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'image' => 'required|string',
            'accuracy' => 'nullable|numeric|min:0',
        ]);

        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;
        // is_mocked is advisory only: it is client-supplied and cannot be
        // trusted by itself. Server-side checks below limit casual abuse.
        $isMocked = (bool) $request->is_mocked;

        if ($isMocked) {
            return response()->json(['success' => false, 'message' => 'Peringatan: Fake GPS terdeteksi! Silakan gunakan lokasi asli.'], 400);
        }

        // BUG-022: reject absurd accuracy claims when the client reports them.
        if ($request->filled('accuracy') && (float) $request->accuracy > 100) {
            return response()->json(['success' => false, 'message' => 'Akurasi GPS terlalu rendah. Aktifkan GPS presisi tinggi lalu coba lagi.'], 400);
        }

        // Haversine formula backend validation
        $earthRadius = 6371000; // in meters
        $latFrom = deg2rad((float) $lokasiSekolah->latitude);
        $lonFrom = deg2rad((float) $lokasiSekolah->longitude);
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
                'message' => 'Anda berada di luar jangkauan lokasi sekolah (' . round($distance) . ' meter).'
            ], 400);
        }

        // Selfie handling
        $img = $request->image;
        if (!str_contains($img, ';base64,')) {
            return response()->json(['success' => false, 'message' => 'Format foto tidak valid.'], 400);
        }

        $image_parts = explode(";base64,", $img);
        $image_base64 = base64_decode($image_parts[1]);
        if ($image_base64 === false || strlen($image_base64) < 100) {
            return response()->json(['success' => false, 'message' => 'Foto selfie tidak dapat diproses.'], 400);
        }

        // BUG-030: only accept real image payload (magic bytes), cap size.
        if (strlen($image_base64) > 8 * 1024 * 1024) {
            return response()->json(['success' => false, 'message' => 'Foto terlalu besar (maks 8MB).'], 400);
        }
        $isPng = str_starts_with($image_base64, "\x89PNG");
        $isJpg = str_starts_with($image_base64, "\xFF\xD8\xFF");
        if (!$isPng && !$isJpg) {
            return response()->json(['success' => false, 'message' => 'Foto selfie tidak valid.'], 400);
        }

        $folderPath = "public/absensi/selfie/";
        $ext = $isJpg ? 'jpg' : 'png';
        $fileName = preg_replace('/[^A-Za-z0-9_-]/', '', $siswa->nis) . "_" . date('Ymd_His') . "." . $ext;

        // BUG-021: atomic duplicate check + writes inside one transaction —
        // two concurrent submissions cannot both insert for the same day.
        $absenHariIni = null;
        $created = null;
        DB::transaction(function () use ($siswa, $lat, $lng, $folderPath, $fileName, $image_base64, &$absenHariIni, &$created) {
            $absenHariIni = Absensi::where('siswa_id', $siswa->id)
                ->whereDate('tanggal', date('Y-m-d'))
                ->lockForUpdate()
                ->first();

            if ($absenHariIni) {
                return;
            }

            Storage::put($folderPath . $fileName, $image_base64);

            // Determine if late
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
            $jamSekarang = date('H:i:s');

            $jadwalHariIni = Jadwal::where('kelas_id', $siswa->kelas_id)
                ->where('hari', $hariIniStr)
                ->orderBy('jam_mulai', 'asc')
                ->first();

            // BUG-024: lateness compares against the class first bell when
            // available, falling back to the global threshold.
            $batasMasuk = $jadwalHariIni && $jadwalHariIni->jam_mulai
                ? (string) $jadwalHariIni->jam_mulai
                : \App\Helpers\SettingHelper::getLateThreshold();

            $isLate = $jamSekarang > $batasMasuk;

            $created = Absensi::create([
                'siswa_id' => $siswa->id,
                'tanggal' => date('Y-m-d'),
                'jam_masuk' => date('H:i:s'),
                'status' => 'hadir',
                'is_late' => $isLate,
                'keterangan' => $isLate ? 'Terlambat' : 'Hadir Tepat Waktu',
                'foto' => $fileName,
                'latitude' => $lat,
                'longitude' => $lng,
            ]);
        });

        if (!$created) {
            return response()->json(['success' => false, 'message' => 'Anda sudah melakukan absensi hari ini.'], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil tersimpan.' . ($created->is_late ? ' (Terlambat)' : '')
        ]);
    }

    public function riwayatKehadiran()
    {
        $user = Auth::user();
        $siswa = null;

        // 1. Logika Penentuan Siswa (Siswa itu sendiri atau Anak dari Orang Tua)
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

        // 2. Ambil Riwayat Keseluruhan
        $riwayat = Absensi::where('siswa_id', $siswa->id)
            ->orderBy('tanggal', 'desc')
            ->get();

        // 3. Statistik Bulanan
        $bulanIni = date('m');
        $tahunIni = date('Y');

        // Helper untuk query status
        $baseQuery = Absensi::where('siswa_id', $siswa->id)
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni);

        $hadirCount = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['hadir'])->count();
        $izinCount  = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['izin'])->count();
        $sakitCount = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['sakit'])->count();
        $alpaCount  = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['alfa'])->count();

        // BUG-027: removed dead effective-day computation (result was
        // discarded; getAlphaCount() below is the single source of truth).

        $rekap = [
            'hadir' => $hadirCount,
            'izin'  => $izinCount,
            'sakit' => $sakitCount,
            'alpa'  => $siswa->getAlphaCount(),
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

        // Bahasa Indonesia Days Mapping
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

        // Cari jadwal mengajar guru ini HARI INI
        $jadwalHariIni = Jadwal::where('guru_id', $guru->id)
            ->where('hari', $hariIniStr)
            ->with('kelas')
            ->get();

        $kelasIds = $jadwalHariIni->pluck('kelas_id')->unique();

        // Ambil semua siswa di kelas tersebut berserta absensi HARI INI
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
        // Defense-in-depth: route also gates with role:guru,admin
        if (!in_array(Auth::user()->role, ['guru', 'admin'], true)) {
            return redirect()->route('dashboard')->with('error', 'Hanya guru yang dapat memperbarui absensi harian.');
        }

        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'status' => 'required|in:Hadir,Izin,Sakit,Alfa',
            'keterangan' => 'nullable|string|max:255'
        ]);

        // BUG-026: write the DB enum's lowercase form regardless of input casing.
        $status = strtolower($request->status);
        $siswa = Siswa::findOrFail($request->siswa_id);
        $tanggalInfo = date('Y-m-d');

        // BUG-021: atomic read-lock-write to prevent duplicate rows per day.
        $absensi = null;
        DB::transaction(function () use ($request, $siswa, $status, $tanggalInfo, &$absensi) {
            $absensi = Absensi::where('siswa_id', $request->siswa_id)
                ->whereDate('tanggal', $tanggalInfo)
                ->lockForUpdate()
                ->first();

            if ($absensi) {
                $absensi->update([
                    'status' => $status,
                    'keterangan' => $request->keterangan ?? 'Diperbarui otomatis oleh Guru'
                ]);
            } else {
                $absensi = Absensi::create([
                    'siswa_id' => $request->siswa_id,
                    'tanggal' => $tanggalInfo,
                    'jam_masuk' => date('H:i:s'),
                    'status' => $status,
                    'keterangan' => $request->keterangan ?? 'Ditambahkan otomatis oleh Guru',
                ]);
            }
        });

        // Point 5: Automated SP System (SP1, SP2, SP3 with dynamic thresholds)
        if ($status === 'alfa') {
            $alfaCount = $siswa->getTotalAlpha();

            // BUG-025: issue every crossed-but-not-yet-issued level, lowest
            // first; the old loop broke on the highest quota and skipped SP1/SP2.
            $spLevels = [
                'SP1' => \App\Helpers\SettingHelper::getAlphaQuota('SP1'),
                'SP2' => \App\Helpers\SettingHelper::getAlphaQuota('SP2'),
                'SP3' => \App\Helpers\SettingHelper::getAlphaQuota('SP3'),
            ];

            foreach ($spLevels as $jenisSP => $kuota) {
                if ($kuota > 0 && $alfaCount >= $kuota) {
                    DB::transaction(function () use ($siswa, $request, $jenisSP, $alfaCount) {
                        $exists = \App\Models\SuratPeringatan::where('siswa_id', $request->siswa_id)
                            ->where('jenis_sp', $jenisSP)
                            ->exists();

                        if (!$exists) {
                            \App\Models\SuratPeringatan::create([
                                'siswa_id' => $request->siswa_id,
                                'nomor_surat' => $jenisSP . '/' . date('Ymd') . '/' . $request->siswa_id,
                                'jenis_sp' => $jenisSP,
                                'tanggal_terbit' => date('Y-m-d'),
                                'keterangan' => "Akumulasi Alfa mencapai {$alfaCount} hari. (Otomatis oleh sistem)"
                            ]);
                        }
                    });
                }
            }
        }

        return redirect()->back()->with('success', 'Status absensi berhasil diperbarui.');
    }

    public function rekapAbsensi(Request $request)
    {
        // BUG-028: non-numeric bulan/tahun previously reached
        // Carbon::createFromDate() unvalidated -> 500.
        $request->validate([
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|between:2000,2100',
        ]);

        $bulan = (int) ($request->bulan ?? date('m'));
        $tahun = (int) ($request->tahun ?? date('Y'));
        $id_kelas = $request->id_kelas;

        $query = Siswa::with('kelas');

        if ($id_kelas) {
            $query->where('kelas_id', $id_kelas);
        }

        $siswas = $query->orderByDesc(
                Absensi::selectRaw('MAX(tanggal)')
                    ->whereColumn('siswa_id', 'siswas.id')
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
            )
            ->orderBy('nama')
            ->get();
        
        $kelasList = \App\Models\Kelas::orderBy('nama_kelas')->get();

        // Hitung hari efektif (kecuali Minggu) di bulan tersebut sampai hari ini atau akhir bulan
        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $endDate = ($bulan == date('m') && $tahun == date('Y'))
            ? Carbon::now()
            : Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();

        $hariEfektif = 0;
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            if ($currentDate->dayOfWeek !== CarbonInterface::SUNDAY) {
                $hariEfektif++;
            }
            $currentDate->addDay();
        }

        // BUG-043: one grouped query replaces 4 counts per student (N+1).
        $counts = Absensi::selectRaw('siswa_id, LOWER(status) as st, COUNT(*) as n')
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
            ->groupBy('siswa_id', 'st')
            ->get()
            ->groupBy('siswa_id')
            ->map(fn ($g) => $g->pluck('n', 'st'));

        $rekapAll = [];
        foreach ($siswas as $s) {
            $c = $counts->get($s->id, collect());
            $hadir = (int) ($c['hadir'] ?? 0);
            $izin = (int) ($c['izin'] ?? 0);
            $sakit = (int) ($c['sakit'] ?? 0);
            $alfaDb = (int) ($c['alfa'] ?? 0);

            $rekapAll[] = [
                'siswa' => $s,
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $s->getAlphaCount($bulan, $tahun),
            ];
        }

        // Paginate array
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

        return view('absensi.rekap-absensi', compact('rekap', 'bulan', 'tahun', 'namaBulan', 'hariEfektif', 'totalSiswa', 'kelasList'));
    }

    public function rekapPerKelas(Request $request)
    {
        // BUG-028: validate filter params (same as rekapAbsensi).
        $request->validate([
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|between:2000,2100',
        ]);

        $bulan = (int) ($request->bulan ?? date('m'));
        $tahun = (int) ($request->tahun ?? date('Y'));

        $kelasList = \App\Models\Kelas::withCount('siswa')->orderBy('nama_kelas')->get();

        // Buat daftar tanggal efektif (kecuali Minggu)
        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $endDate = ($bulan == date('m') && $tahun == date('Y'))
            ? Carbon::now()
            : Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();

        $tanggalList = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            if ($currentDate->dayOfWeek !== CarbonInterface::SUNDAY) {
                $tanggalList[] = $currentDate->format('Y-m-d');
            }
            $currentDate->addDay();
        }

        // Urutkan tanggal terbaru di atas
        $tanggalList = array_reverse($tanggalList);

        // Build rekap: tiap tanggal -> tiap kelas -> count status
        $rekapAll = [];
        foreach ($tanggalList as $tgl) {
            foreach ($kelasList as $kelas) {
                // Only count students who were already registered on or before this date
                $siswaIds = Siswa::where('kelas_id', $kelas->id)
                    ->whereDate('created_at', '<=', $tgl)
                    ->pluck('id');
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
                    'tanggal' => $tgl,
                    'kelas' => $kelas->nama_kelas,
                    'total_siswa' => $totalSiswa,
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'alpa' => $alpa,
                ];
            }
        }

        // Paginate array
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
