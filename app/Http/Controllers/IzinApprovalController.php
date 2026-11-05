<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Izin;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IzinApprovalController extends Controller
{
    /**
     * Approval queue, scoped for guru (own classes) and kaprog (own major).
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Izin::with(['siswa.kelas.jurusan', 'decidedBy']);

        // Scope: guru sees requests from students they teach / are homeroom of.
        if ($user->role === 'guru') {
            $query->whereHas('siswa', function ($q) use ($user) {
                $q->whereIn('kelas_id', $this->guruClassIds($user));
            });
        } elseif ($user->role === 'kaprog' && $user->jurusan_id) {
            $query->whereHas('siswa.kelas', fn ($q) => $q->where('jurusan_id', $user->jurusan_id));
        }

        $status = $request->get('status', 'pending');
        if (in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                  ->orWhere('nis', 'LIKE', '%' . $search . '%');
            });
        }

        $izins = $query->orderBy('tanggal')->orderBy('id')->paginate(20)->withQueryString();

        $pendingCount = (clone $query)->where('status', 'pending')->count();

        return view('izin.approval', [
            'izins'        => $izins,
            'status'       => $status,
            'search'       => $request->search ?? '',
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Approve: mark approved and write the attendance record atomically.
     */
    public function approve(Request $request, $id)
    {
        $request->validate(['catatan' => 'nullable|string|max:500']);

        $izin = Izin::with('siswa')->findOrFail($id);
        $this->assertCanDecide($izin);

        if ($izin->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($izin, $request) {
            $izin->update([
                'status'            => 'approved',
                'keputusan_catatan' => $request->catatan,
                'decided_by'        => Auth::id(),
                'decided_at'        => now(),
            ]);

            // Write attendance only now that it is verified. updateOrCreate
            // tolerates an existing row (e.g. a manual entry made meanwhile).
            Absensi::updateOrCreate(
                ['siswa_id' => $izin->siswa_id, 'tanggal' => $izin->tanggal->toDateString()],
                [
                    'status'     => $izin->jenis,
                    'keterangan' => 'Izin disetujui: ' . $izin->keterangan,
                ]
            );
        });

        try {
            app(\App\Services\InAppNotificationService::class)->notifyLeaveStatusUpdated($izin, 'approved', Auth::user()->name);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Notification failed for izin {$izin->id} approval: " . $e->getMessage());
        }

        return back()->with('success', "Pengajuan {$izin->jenis} {$izin->siswa->nama} disetujui.");
    }

    /**
     * Reject: mark rejected, no attendance written.
     */
    public function reject(Request $request, $id)
    {
        $request->validate(['catatan' => 'nullable|string|max:500']);

        $izin = Izin::with('siswa')->findOrFail($id);
        $this->assertCanDecide($izin);

        if ($izin->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $izin->update([
            'status'            => 'rejected',
            'keputusan_catatan' => $request->catatan,
            'decided_by'        => Auth::id(),
            'decided_at'        => now(),
        ]);

        try {
            app(\App\Services\InAppNotificationService::class)->notifyLeaveStatusUpdated($izin, 'rejected', Auth::user()->name);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Notification failed for izin {$izin->id} rejection: " . $e->getMessage());
        }

        return back()->with('success', "Pengajuan {$izin->jenis} {$izin->siswa->nama} ditolak.");
    }

    /**
     * Class ids a guru is allowed to decide for.
     */
    private function guruClassIds($user)
    {
        $guru = Guru::with(['activeHomeroomAssignment', 'activeSubjectAssignments'])
            ->where('user_id', $user->id)
            ->first();

        if (!$guru) {
            return collect();
        }

        return $guru->activeSubjectAssignments->pluck('kelas_id')
            ->merge($guru->activeJadwal()->pluck('kelas_id'))
            ->merge($guru->activeHomeroomAssignment ? collect([$guru->activeHomeroomAssignment->kelas_id]) : collect())
            ->unique()
            ->values();
    }

    /**
     * Authorise the acting user for this request.
     */
    private function assertCanDecide(Izin $izin): void
    {
        $user = Auth::user();

        if (in_array($user->role, ['superadmin', 'admin'])) {
            return;
        }

        if ($user->role === 'kaprog') {
            abort_unless(
                $user->jurusan_id && (int) $izin->siswa?->kelas?->jurusan_id === (int) $user->jurusan_id,
                403,
                'Akses ditolak: siswa ini bukan bagian dari jurusan Anda.'
            );
            return;
        }

        if ($user->role === 'guru') {
            abort_unless(
                $this->guruClassIds($user)->contains($izin->siswa?->kelas_id),
                403,
                'Akses ditolak: siswa ini bukan bagian dari kelas yang Anda ampu.'
            );
            return;
        }

        abort(403, 'Akses ditolak.');
    }
}
