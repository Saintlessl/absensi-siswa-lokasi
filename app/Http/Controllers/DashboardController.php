<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Jurusan;
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

        if (in_array($user->role, ['superadmin', 'admin', 'kaprog'])) {
            $bulan = $request->bulan ?? date('m');
            $tahun = $request->tahun ?? date('Y');
            $search = $request->search ?? '';

            if ($user->role === 'kaprog') {
                $guru = Guru::where('user_id', $user->id)->first();
                $jurusanId = $guru ? $guru->user->jurusan_id : null;

                if ($jurusanId) {
                    $data['totalSiswa'] = Siswa::whereHas('kelas', fn($q) => $q->where('jurusan_id', $jurusanId))->count();
                    $data['totalGuru'] = Guru::whereHas('user', fn($q) => $q->where('jurusan_id', $jurusanId))->count() ?: Guru::count();
                    $data['totalKelas'] = Kelas::where('jurusan_id', $jurusanId)->count();
                    $data['hadirHariIni'] = Absensi::whereDate('tanggal', date('Y-m-d'))
                        ->whereHas('siswa.kelas', fn($q) => $q->where('jurusan_id', $jurusanId))
                        ->whereRaw('LOWER(status) = ?', ['hadir'])->count();

                    $query = Absensi::with('siswa.kelas')
                        ->whereHas('siswa.kelas', fn($q) => $q->where('jurusan_id', $jurusanId))
                        ->whereMonth('tanggal', $bulan)
                        ->whereYear('tanggal', $tahun)
                        ->orderBy('tanggal', 'desc')
                        ->orderBy('id', 'desc');
                } else {
                    $data['totalSiswa'] = Siswa::count();
                    $data['totalGuru'] = Guru::count();
                    $data['totalKelas'] = Kelas::count();
                    $data['hadirHariIni'] = Absensi::whereDate('tanggal', date('Y-m-d'))
                        ->whereRaw('LOWER(status) = ?', ['hadir'])->count();

                    $query = Absensi::with('siswa.kelas')
                        ->whereMonth('tanggal', $bulan)
                        ->whereYear('tanggal', $tahun)
                        ->orderBy('tanggal', 'desc')
                        ->orderBy('id', 'desc');
                }
            } else {
                $data['totalSiswa'] = Siswa::count();
                $data['totalGuru'] = Guru::count();
                $data['totalKelas'] = Kelas::count();

                // Absensi hari ini
                $data['hadirHariIni'] = Absensi::whereDate('tanggal', date('Y-m-d'))
                    ->whereRaw('LOWER(status) = ?', ['hadir'])->count();

                $query = Absensi::with('siswa.kelas')
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->orderBy('tanggal', 'desc')
                    ->orderBy('id', 'desc');
            }

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

            if ($orangTua) {
                // Only resolve students through the explicit pivot; never use a
                // legacy foreign key to infer authorization.
                $siswa = $orangTua->siswa()->with('kelas')->first();
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
            $siswa = Siswa::with(['kelas.jurusan', 'faceBiometric'])->where('user_id', $user->id)->first();
            $data['siswa'] = $siswa;

            if ($siswa) {
                // Cek status absensi hari ini
                $data['absenHariIni'] = Absensi::where('siswa_id', $siswa->id)
                    ->whereDate('tanggal', date('Y-m-d'))
                    ->first();

                $bulan = $request->bulan ?? date('m');
                $tahun = $request->tahun ?? date('Y');
                $data['bulan'] = $bulan;
                $data['tahun'] = $tahun;

                // Rekap bulan ini
                $hadirBulanIni = Absensi::where('siswa_id', $siswa->id)
                    ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                    ->whereRaw('LOWER(status) = ?', ['hadir'])->count();

                $terlambatBulanIni = Absensi::where('siswa_id', $siswa->id)
                    ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                    ->where('menit_terlambat', '>', 0)->count();

                $izinBulanIni = Absensi::where('siswa_id', $siswa->id)
                    ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                    ->whereRaw('LOWER(status) = ?', ['izin'])->count();

                $sakitBulanIni = Absensi::where('siswa_id', $siswa->id)
                    ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                    ->whereRaw('LOWER(status) = ?', ['sakit'])->count();

                $data['statsSiswa'] = [
                    'hadir'     => $hadirBulanIni,
                    'terlambat' => $terlambatBulanIni,
                    'izin'      => $izinBulanIni,
                    'sakit'     => $sakitBulanIni,
                ];

                // Riwayat absensi terbaru
                $data['riwayatSiswa'] = Absensi::where('siswa_id', $siswa->id)
                    ->orderBy('tanggal', 'desc')
                    ->limit(7)
                    ->get();

                $data['tanggalHariIni'] = \Carbon\Carbon::now()->translatedFormat('l, d F Y');
            }
        }
        if (in_array($user->role, ['guru', 'kaprog'])) {
            $guru = Guru::with([
                'user.jurusan',
                'activeHomeroomAssignment.kelas.jurusan',
                'activeSubjectAssignments.mataPelajaran',
                'activeSubjectAssignments.kelas.jurusan'
            ])->where('user_id', $user->id)->first();

            $data['guru'] = $guru;

            if ($guru) {
                $now = \Carbon\Carbon::now();
                $data['tanggalHariIni'] = $now->isoFormat('dddd, D MMMM Y');
                $data['jamSekarang'] = $now->format('H:i');

                $hariIni = $now->locale('id')->dayName;

                // 1. Live schedules for today with physical room resolution
                $scheduleService = app(\App\Services\TeacherScheduleService::class);
                $workloadService = app(\App\Services\TeacherWorkloadService::class);

                $data['jadwalHariIni'] = $scheduleService->getTeacherSchedulesWithLiveRooms($guru, $hariIni, $now);
                $data['workload'] = $workloadService->calculateWorkload($guru);

                // 2. Homeroom Monitoring (if teacher is active Wali Kelas)
                $activeHomeroom = $guru->activeHomeroomAssignment;
                $data['activeHomeroom'] = $activeHomeroom;

                if ($activeHomeroom) {
                    $homeroomKelas = $activeHomeroom->kelas;
                    $todayStr = $now->toDateString();

                    $studentIds = Siswa::where('kelas_id', $homeroomKelas->id)->pluck('id');
                    $totalHomeroomStudents = $studentIds->count();

                    $todayAttendance = Absensi::whereIn('siswa_id', $studentIds)
                        ->whereDate('tanggal', $todayStr)
                        ->get();

                    // Absensi status is stored lowercase ('hadir' etc.) but some writers use
                    // capitalised values; compare case-insensitively so izin/sakit
                    // are not silently counted as alpa.
                    $hadirCount = $todayAttendance->filter(fn ($a) => strtolower((string) $a->status) === 'hadir')->count();
                    $terlambatCount = $todayAttendance->where('menit_terlambat', '>', 0)->count();
                    $izinCount = $todayAttendance->filter(fn ($a) => strtolower((string) $a->status) === 'izin')->count();
                    $sakitCount = $todayAttendance->filter(fn ($a) => strtolower((string) $a->status) === 'sakit')->count();
                    $belumAbsenCount = max(0, $totalHomeroomStudents - $todayAttendance->count());

                    $hadirStudentIds = $todayAttendance
                        ->filter(fn ($a) => strtolower((string) $a->status) === 'hadir')
                        ->pluck('siswa_id');

                    $attentionStudents = Siswa::where('kelas_id', $homeroomKelas->id)
                        ->whereNotIn('id', $hadirStudentIds)
                        ->limit(6)
                        ->get();

                    $waSentCount = \App\Models\WhatsappNotificationLog::whereIn('siswa_id', $studentIds)
                        ->whereDate('created_at', $todayStr)
                        ->where('status', 'sent')
                        ->count();

                    $data['homeroomStats'] = [
                        'kelas'              => $homeroomKelas,
                        'total_students'     => $totalHomeroomStudents,
                        'hadir'              => $hadirCount,
                        'terlambat'          => $terlambatCount,
                        'izin'               => $izinCount,
                        'sakit'              => $sakitCount,
                        'belum_absen'        => $belumAbsenCount,
                        'attention_students' => $attentionStudents,
                        'wa_sent_count'      => $waSentCount,
                    ];
                }

                // 3. KAPROG Overview (if teacher is Head of Department)
                $isKaprog = $guru->is_kaprog;
                $data['isKaprog'] = $isKaprog;
                if ($isKaprog && $guru->user->jurusan_id) {
                    $jurusan = \App\Models\Jurusan::withCount(['activeKelas'])->find($guru->user->jurusan_id);
                    $data['kaprogJurusan'] = $jurusan;
                }
            }
        }
        return view('dashboard.index', $data);
    }

    public function deleteAbsensi($id)
    {
        if (Auth::user()->role !== 'superadmin') {
            return redirect()->back()->with('error', 'Akses ditolak: Hanya Superadmin yang memiliki izin untuk menghapus data riwayat absensi.');
        }

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
        $user = Auth::user();
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $search = $request->search ?? '';

        $query = Absensi::with('siswa.kelas')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        if ($user && $user->role === 'kaprog') {
            $guru = Guru::where('user_id', $user->id)->first();
            if ($guru && $guru->user->jurusan_id) {
                $query->whereHas('siswa.kelas', fn($q) => $q->where('jurusan_id', $guru->user->jurusan_id));
            }
        }

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
