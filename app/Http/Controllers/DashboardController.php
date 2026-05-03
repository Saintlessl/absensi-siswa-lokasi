<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Exports\AbsensiExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $data = [];

        if ($user->role === 'admin') {
            $data['totalSiswa'] = Siswa::count();
            $data['totalGuru'] = Guru::count();
            $data['totalKelas'] = Kelas::count();

            // Absensi hari ini
            $data['hadirHariIni'] = Absensi::whereDate('tanggal', date('Y-m-d'))
                ->whereRaw('LOWER(status) = ?', ['hadir'])->count();

            // Filter
            $bulan = $request->bulan ?? date('m');
            $tahun = $request->tahun ?? date('Y');
            $search = $request->search ?? '';

            $query = Absensi::with('siswa.kelas')
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->orderBy('tanggal', 'desc')
                ->orderBy('id', 'desc');

            if ($search) {
                $query->whereHas('siswa', function ($q) use ($search) {
                    $q->where('nama', 'LIKE', '%' . $search . '%')
                        ->orWhere('nis', 'LIKE', '%' . $search . '%');
                });
            }

            $data['absensi'] = $query->paginate(10)->appends($request->query());
            $data['bulan'] = $bulan;
            $data['tahun'] = $tahun;
            $data['search'] = $search;
        }

        if ($user->role === 'orangtua') {
            $orangTua = \App\Models\OrangTua::where('user_id', $user->id)->first();

            if ($orangTua && $orangTua->siswa_id) {
                $siswa = Siswa::with('kelas')->find($orangTua->siswa_id);
                $data['siswa'] = $siswa;

                $bulan = $request->bulan ?? date('m');
                $tahun = $request->tahun ?? date('Y');
                $data['bulan'] = $bulan;
                $data['tahun'] = $tahun;

                if ($siswa) {
                    // Status hari ini
                    $data['absenHariIni'] = Absensi::where('siswa_id', $siswa->id)
                        ->whereDate('tanggal', date('Y-m-d'))->first();

                    // Rekap bulan ini
                    $hadirCount = Absensi::where('siswa_id', $siswa->id)
                        ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                        ->whereRaw('LOWER(status) = ?', ['hadir'])->count();
                    $izinCount = Absensi::where('siswa_id', $siswa->id)
                        ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                        ->whereRaw('LOWER(status) = ?', ['izin'])->count();
                    $sakitCount = Absensi::where('siswa_id', $siswa->id)
                        ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                        ->whereRaw('LOWER(status) = ?', ['sakit'])->count();

                    // Hitung hari efektif
                    $startDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
                    $endDate = ($bulan == date('m') && $tahun == date('Y'))
                        ? \Carbon\Carbon::now()
                        : \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
                    $hariEfektif = 0;
                    $cur = $startDate->copy();
                    while ($cur->lte($endDate)) {
                        if ($cur->dayOfWeek !== \Carbon\Carbon::SUNDAY) $hariEfektif++;
                        $cur->addDay();
                    }

                    $alfaDb = Absensi::where('siswa_id', $siswa->id)
                        ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                        ->whereRaw('LOWER(status) = ?', ['alfa'])->count();
                    $totalTercatat = $hadirCount + $izinCount + $sakitCount + $alfaDb;
                    $alpaCount = $alfaDb + max(0, $hariEfektif - $totalTercatat);

                    $total = $hadirCount + $izinCount + $sakitCount + $alpaCount;
                    $data['rekap'] = [
                        'hadir' => $hadirCount,
                        'izin' => $izinCount,
                        'sakit' => $sakitCount,
                        'alpa' => $alpaCount,
                        'total' => $total,
                        'persen_hadir' => $total > 0 ? round(($hadirCount / $total) * 100) : 0,
                        'persen_izin' => $total > 0 ? round(($izinCount / $total) * 100) : 0,
                        'persen_sakit' => $total > 0 ? round(($sakitCount / $total) * 100) : 0,
                        'persen_alpa' => $total > 0 ? round(($alpaCount / $total) * 100) : 0,
                    ];
                    $data['hariEfektif'] = $hariEfektif;

                    // Riwayat harian terbaru (10 terakhir)
                    $data['riwayatHarian'] = Absensi::where('siswa_id', $siswa->id)
                        ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                        ->orderBy('tanggal', 'desc')->limit(10)->get();
                }
            }
        }
        if ($user->role === 'siswa') {
            $siswa = Siswa::where('user_id', $user->id)->first();
            $data['siswa'] = $siswa;

            if ($siswa) {
                // Cek status absensi hari ini
                $data['absenHariIni'] = Absensi::where('siswa_id', $siswa->id)
                    ->whereDate('tanggal', date('Y-m-d'))
                    ->first();

                // Tambahkan data waktu untuk jam digital di view
                $data['tanggalHariIni'] = \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y');
            }
        }
        if ($user->role === 'guru') {
            $guru = \App\Models\Guru::where('user_id', $user->id)->first();
            $data['guru'] = $guru;

            if ($guru) {
                $now = \Carbon\Carbon::now();
                $data['tanggalHariIni'] = $now->isoFormat('dddd, D MMMM Y');
                $data['jamSekarang'] = $now->format('H:i');

                // Ambil nama hari dalam Bahasa Indonesia untuk filter database
                $hariIni = $now->locale('id')->dayName;

                $data['jadwalHariIni'] = \App\Models\Jadwal::with('kelas')
                    ->where('guru_id', $guru->id)
                    ->where('hari', $hariIni)
                    ->orderBy('jam_mulai', 'asc')
                    ->get();
            }
        }
        return view('dashboard.index', $data);
    }

    public function deleteAbsensi($id)
    {
        $absensi = Absensi::findOrFail($id);
        $absensi->delete();

        return redirect()->back()->with('success', 'Data absensi berhasil dihapus.');
    }

    public function updateAbsensi(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Hadir,Izin,Sakit,Alfa',
            'keterangan' => 'nullable|string'
        ]);

        $absensi = Absensi::findOrFail($id);
        $absensi->update([
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Data absensi berhasil diperbarui.');
    }

    /**
     * Export absensi data to PDF
     */
    public function exportPdf(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $search = $request->search ?? '';

        $query = Absensi::with('siswa.kelas')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        if ($search) {
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                    ->orWhere('nis', 'LIKE', '%' . $search . '%');
            });
        }

        $absensi = $query->get();
        $namaBulan = \Carbon\Carbon::createFromDate(null, $bulan, 1)->isoFormat('MMMM');

        $pdf = Pdf::loadView('exports.absensi-pdf', compact('absensi', 'bulan', 'tahun', 'search'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("Rekap_Absensi_{$namaBulan}_{$tahun}.pdf");
    }

    /**
     * Export absensi data to Excel
     */
    public function exportExcel(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $search = $request->search ?? '';

        $namaBulan = \Carbon\Carbon::createFromDate(null, $bulan, 1)->isoFormat('MMMM');

        return Excel::download(
            new AbsensiExport($bulan, $tahun, $search),
            "Rekap_Absensi_{$namaBulan}_{$tahun}.xlsx"
        );
    }
}
