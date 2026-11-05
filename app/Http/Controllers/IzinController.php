<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Izin;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IzinController extends Controller
{
    /**
     * Student's leave-request page: form + their pending/history list.
     */
    public function index()
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak terdaftar sebagai Siswa aktif.');
        }

        // Cek absen hari ini
        $absenHariIni = Absensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', date('Y-m-d'))
            ->first();

        $riwayat = Izin::with('decidedBy')->where('siswa_id', $siswa->id)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get();

        return view('izin.index', compact('siswa', 'absenHariIni', 'riwayat'));
    }

    /**
     * Submit a leave request. Only creates a PENDING izins row; attendance is
     * only written after an approver approves it.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Anda bukan siswa');
        }

        $request->validate([
            'tanggal'    => 'required|date|after_or_equal:today',
            'jenis'      => 'required|in:izin,sakit',
            'keterangan' => 'required|string|max:500',
        ]);

        $cekAbsen = Absensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', $request->tanggal)
            ->exists();

        if ($cekAbsen) {
            return redirect()->back()->with('error', 'Anda sudah memiliki rekam kehadiran pada tanggal tersebut.');
        }

        $existsPending = Izin::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', $request->tanggal)
            ->exists();

        if ($existsPending) {
            return redirect()->back()->with('error', 'Pengajuan izin untuk tanggal tersebut sudah ada.');
        }

        $izin = Izin::create([
            'siswa_id'   => $siswa->id,
            'tanggal'    => $request->tanggal,
            'jenis'      => $request->jenis,
            'keterangan' => $request->keterangan,
            'status'     => 'pending',
        ]);

        try {
            app(\App\Services\InAppNotificationService::class)->notifyLeaveRequested($izin);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Notification failed for izin {$izin->id}: " . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Pengajuan ' . ucfirst($request->jenis) . ' terkirim dan menunggu persetujuan.');
    }

    /**
     * Cancel a still-pending request (student's own).
     */
    public function cancel(Request $request, $id)
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        $izin = Izin::where('id', $id)
            ->when($siswa, fn ($q) => $q->where('siswa_id', $siswa->id))
            ->firstOrFail();

        if ($izin->status !== 'pending') {
            return back()->with('error', 'Pengajuan yang sudah diproses tidak dapat dibatalkan.');
        }

        $izin->delete();

        return back()->with('success', 'Pengajuan izin dibatalkan.');
    }
}
