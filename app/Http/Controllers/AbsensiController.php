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

        // Cek apakah sudah absen hari ini
        $cekAbsen = Absensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', date('Y-m-d'))
            ->exists();

        if ($cekAbsen) {
            return response()->json(['success' => false, 'message' => 'Anda sudah melakukan absensi hari ini.'], 400);
        }

        $lat = $request->latitude;
        $lng = $request->longitude;

        if (!$lat || !$lng) {
            return response()->json(['success' => false, 'message' => 'Lokasi Anda tidak terdeteksi.'], 400);
        }

        // Haversine formula backend validation
        $earthRadius = 6371000; // in meters
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
                'message' => 'Anda berada di luar jangkauan lokasi sekolah (' . round($distance) . ' meter).'
            ], 400);
        }

        Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal' => date('Y-m-d'),
            'jam_masuk' => date('H:i:s'),
            'status' => 'Hadir',
            'keterangan' => 'Hadir',
            'latitude' => $lat,
            'longitude' => $lng,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil tersimpan.'
        ]);
    }


    public function riwayatKehadiran()
    {
        $user = Auth::user();
        $siswa = null;

        // 1. Logika Penentuan Siswa (Siswa itu sendiri atau Anak dari Orang Tua)
        if ($user->role == 'orangtua') {
            $dataOrangTua = \App\Models\OrangTua::where('user_id', $user->id)->first();
            if ($dataOrangTua) {
                $siswa = \App\Models\Siswa::find($dataOrangTua->siswa_id);
            }
        } else {
            $siswa = \App\Models\Siswa::where('user_id', $user->id)->first();
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

        // 4. Perhitungan Hari Efektif (Menghindari Alpa manual yang tidak terinput)
        $startDate = \Carbon\Carbon::now()->startOfMonth();
        $endDate = \Carbon\Carbon::now();
        $hariEfektif = 0;

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            // Asumsi hari minggu libur
            if ($currentDate->dayOfWeek !== \Carbon\Carbon::SUNDAY) {
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
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'status' => 'required|in:Hadir,Izin,Sakit,Alfa',
            'keterangan' => 'nullable|string'
        ]);

        $tanggalInfo = date('Y-m-d');

        $absensi = Absensi::where('siswa_id', $request->siswa_id)
            ->whereDate('tanggal', $tanggalInfo)
            ->first();

        if ($absensi) {
            $absensi->update([
                'status' => $request->status,
                'keterangan' => $request->keterangan ?? 'Diperbarui otomatis oleh Guru'
            ]);
        } else {
            Absensi::create([
                'siswa_id' => $request->siswa_id,
                'tanggal' => $tanggalInfo,
                'jam_masuk' => date('H:i:s'),
                'status' => $request->status,
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

        // Hitung hari efektif (kecuali Minggu) di bulan tersebut sampai hari ini atau akhir bulan
        $startDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $endDate = ($bulan == date('m') && $tahun == date('Y'))
            ? \Carbon\Carbon::now()
            : \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();

        $hariEfektif = 0;
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            if ($currentDate->dayOfWeek !== \Carbon\Carbon::SUNDAY) {
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
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $alpa,
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

        $namaBulan = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->isoFormat('MMMM Y');

        return view('absensi.rekap-absensi', compact('rekap', 'bulan', 'tahun', 'namaBulan', 'hariEfektif', 'totalSiswa'));
    }

    public function rekapPerKelas(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $kelasList = \App\Models\Kelas::withCount('siswa')->orderBy('nama_kelas')->get();

        // Buat daftar tanggal efektif (kecuali Minggu)
        $startDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $endDate = ($bulan == date('m') && $tahun == date('Y'))
            ? \Carbon\Carbon::now()
            : \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();

        $tanggalList = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            if ($currentDate->dayOfWeek !== \Carbon\Carbon::SUNDAY) {
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

        $namaBulan = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->isoFormat('MMMM Y');

        return view('absensi.rekap-kelas', compact('rekap', 'bulan', 'tahun', 'namaBulan', 'totalBaris'));
    }
}
