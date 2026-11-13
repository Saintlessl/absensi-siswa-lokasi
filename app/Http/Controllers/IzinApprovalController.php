<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\AbsensiGuru;
use App\Models\AuditLog;
use App\Models\Guru;
use App\Models\Izin;
use App\Models\Siswa;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IzinApprovalController extends Controller
{
    /**
     * Unified Approval Queue for both Students & Teachers
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if user has permission to view at least one category
        $canViewSiswa = $user->hasRole('superadmin') || $user->hasPermission('izin.view_siswa');
        $canViewGuru  = $user->hasRole('superadmin') || $user->hasPermission('izin.view_guru');

        if (!$canViewSiswa && !$canViewGuru) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk melihat data persetujuan izin.');
        }

        $query = Izin::with(['siswa.kelas.jurusan', 'guru', 'decidedBy']);

        // Scope based on permissions
        if ($canViewSiswa && !$canViewGuru) {
            $query->where('applicant_type', 'siswa');
        } elseif (!$canViewSiswa && $canViewGuru) {
            $query->where('applicant_type', 'guru');
        }

        // Department and class scopes for student requests
        if ($user->hasRole('kaprog') && $user->jurusan_id) {
            $query->where(function ($q) use ($user) {
                $q->where('applicant_type', 'guru')
                  ->orWhereHas('siswa.kelas', fn ($sub) => $sub->where('jurusan_id', $user->jurusan_id));
            });
        } elseif ($user->hasRole('guru')) {
            $classIds = $this->guruClassIds($user);
            $query->where(function ($q) use ($classIds) {
                $q->where('applicant_type', 'guru')
                  ->orWhereHas('siswa', fn ($sub) => $sub->whereIn('kelas_id', $classIds));
            });
        }

        // Filter: Applicant Type (Semua / Siswa / Guru)
        $tipe = $request->get('tipe', 'all');
        if ($tipe === 'siswa') {
            $query->where('applicant_type', 'siswa');
        } elseif ($tipe === 'guru') {
            $query->where('applicant_type', 'guru');
        }

        // Filter: Status (all, pending, approved, rejected, cancelled)
        $status = $request->get('status', 'pending');
        if (in_array($status, ['pending', 'approved', 'rejected', 'cancelled'])) {
            $query->where('status', $status);
        }

        // Search: Name, NIS, NIP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('siswa', function ($sub) use ($search) {
                    $sub->where('nama', 'LIKE', "%{$search}%")
                        ->orWhere('nis', 'LIKE', "%{$search}%");
                })->orWhereHas('guru', function ($sub) use ($search) {
                    $sub->where('nama', 'LIKE', "%{$search}%")
                        ->orWhere('nip', 'LIKE', "%{$search}%");
                });
            });
        }

        $izins = $query->orderByDesc('created_at')->orderBy('id')->paginate(15)->withQueryString();

        // Tab counts
        $baseCountQuery = clone $query;
        $pendingCount  = (clone $query)->where('status', 'pending')->count();
        $approvedCount = Izin::where('status', 'approved')->count();
        $rejectedCount = Izin::where('status', 'rejected')->count();

        // Breakdown counts for badges
        $pendingSiswaCount = Izin::where('applicant_type', 'siswa')->where('status', 'pending')->count();
        $pendingGuruCount  = Izin::where('applicant_type', 'guru')->where('status', 'pending')->count();

        $canApproveSiswa = $user->hasRole('superadmin') || $user->hasPermission('izin.approve_siswa');
        $canApproveGuru  = $user->hasRole('superadmin') || $user->hasPermission('izin.approve_guru');

        return view('izin.approval', [
            'izins'             => $izins,
            'status'            => $status,
            'tipe'              => $tipe,
            'search'            => $request->search ?? '',
            'pendingCount'      => $pendingCount,
            'approvedCount'     => $approvedCount,
            'rejectedCount'     => $rejectedCount,
            'pendingSiswaCount' => $pendingSiswaCount,
            'pendingGuruCount'  => $pendingGuruCount,
            'canApproveSiswa'   => $canApproveSiswa,
            'canApproveGuru'    => $canApproveGuru,
        ]);
    }

    /**
     * Approve leave request and synchronize with attendance records atomically
     */
    public function approve(Request $request, $id)
    {
        $request->validate(['catatan' => 'nullable|string|max:500']);

        $izin = Izin::with(['siswa', 'guru'])->findOrFail($id);
        $this->assertCanDecide($izin, 'approve');

        if ($izin->status !== 'pending') {
            return back()->with('error', 'Permohonan izin ini sudah diproses sebelumnya.');
        }

        $oldValues = $izin->toArray();

        DB::transaction(function () use ($izin, $request) {
            $izin->update([
                'status'            => 'approved',
                'keputusan_catatan' => $request->catatan,
                'decided_by'        => Auth::id(),
                'decided_at'        => now(),
            ]);

            // Resolve date range (single day or multiple days)
            $start = $izin->tanggal_mulai ?? $izin->tanggal;
            $end = $izin->tanggal_selesai ?? $start;

            $period = CarbonPeriod::create($start, $end);

            if ($izin->applicant_type === 'siswa') {
                $statusKehadiran = in_array($izin->jenis, ['izin', 'sakit']) ? $izin->jenis : 'izin';
                foreach ($period as $date) {
                    Absensi::updateOrCreate(
                        [
                            'siswa_id' => $izin->siswa_id,
                            'tanggal'  => $date->toDateString(),
                        ],
                        [
                            'status'     => $statusKehadiran,
                            'keterangan' => 'Izin disetujui: ' . $izin->keterangan,
                        ]
                    );
                }
            } elseif ($izin->applicant_type === 'guru') {
                $statusKehadiran = in_array($izin->jenis, ['izin', 'sakit', 'dinas_luar']) ? $izin->jenis : 'izin';
                foreach ($period as $date) {
                    AbsensiGuru::updateOrCreate(
                        [
                            'guru_id' => $izin->guru_id,
                            'tanggal' => $date->toDateString(),
                        ],
                        [
                            'status'     => $statusKehadiran,
                            'keterangan' => 'Izin disetujui: ' . $izin->keterangan,
                            'izin_id'    => $izin->id,
                        ]
                    );
                }
            }
        });

        // Audit Log
        AuditLog::log(
            'izin.approve',
            $izin,
            $oldValues,
            $izin->toArray(),
            "Menyetujui izin {$izin->applicant_type} '{$izin->applicant_name}' ({$izin->jenis_label})"
        );

        // In-app notification attempt
        try {
            app(\App\Services\InAppNotificationService::class)->notifyLeaveStatusUpdated($izin, 'approved', Auth::user()->name);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Notification failed for izin {$izin->id} approval: " . $e->getMessage());
        }

        return back()->with('success', "Permohonan izin {$izin->applicant_name} ({$izin->jenis_label}) berhasil disetujui.");
    }

    /**
     * Reject leave request with mandatory or optional notes
     */
    public function reject(Request $request, $id)
    {
        $request->validate(['catatan' => 'nullable|string|max:500']);

        $izin = Izin::with(['siswa', 'guru'])->findOrFail($id);
        $this->assertCanDecide($izin, 'reject');

        if ($izin->status !== 'pending') {
            return back()->with('error', 'Permohonan izin ini sudah diproses sebelumnya.');
        }

        $oldValues = $izin->toArray();

        $izin->update([
            'status'            => 'rejected',
            'keputusan_catatan' => $request->catatan,
            'decided_by'        => Auth::id(),
            'decided_at'        => now(),
        ]);

        AuditLog::log(
            'izin.reject',
            $izin,
            $oldValues,
            $izin->toArray(),
            "Menolak permohonan izin {$izin->applicant_type} '{$izin->applicant_name}' ({$izin->jenis_label})"
        );

        try {
            app(\App\Services\InAppNotificationService::class)->notifyLeaveStatusUpdated($izin, 'rejected', Auth::user()->name);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Notification failed for izin {$izin->id} rejection: " . $e->getMessage());
        }

        return back()->with('success', "Permohonan izin {$izin->applicant_name} berhasil ditolak.");
    }

    /**
     * Authorize decision capability dynamically
     */
    private function assertCanDecide(Izin $izin, string $action = 'approve'): void
    {
        $user = Auth::user();

        // 1. Superadmin always authorized
        if ($user->hasRole('superadmin')) {
            return;
        }

        // 2. Permission check based on applicant type
        if ($izin->applicant_type === 'guru') {
            $requiredPermission = $action === 'approve' ? 'izin.approve_guru' : 'izin.reject_guru';
            abort_unless(
                $user->hasPermission($requiredPermission),
                403,
                'Akses ditolak: Anda tidak memiliki hak akses untuk memproses permohonan izin guru.'
            );
            return;
        }

        if ($izin->applicant_type === 'siswa') {
            $requiredPermission = $action === 'approve' ? 'izin.approve_siswa' : 'izin.reject_siswa';

            // Guru class check: check if user teaches or is homeroom of the student's class
            $isClassTeacher = $user->hasRole('guru') && $this->guruClassIds($user)->contains($izin->siswa?->kelas_id);
            $hasPermission = $user->hasPermission($requiredPermission) || $isClassTeacher;

            abort_unless(
                $hasPermission,
                403,
                'Akses ditolak: Anda tidak memiliki hak akses untuk memproses permohonan izin siswa.'
            );

            // Kaprog department check
            if ($user->hasRole('kaprog')) {
                abort_unless(
                    $user->jurusan_id && (int) $izin->siswa?->kelas?->jurusan_id === (int) $user->jurusan_id,
                    403,
                    'Akses ditolak: Siswa ini bukan bagian dari jurusan yang Anda kepalai.'
                );
                return;
            }

            // Guru class check
            if ($user->hasRole('guru')) {
                abort_unless(
                    $isClassTeacher,
                    403,
                    'Akses ditolak: Siswa ini bukan bagian dari kelas yang Anda ampu.'
                );
                return;
            }

            return;
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * Classes assigned to this teacher
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
}
