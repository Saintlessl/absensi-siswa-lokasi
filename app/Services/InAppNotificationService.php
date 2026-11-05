<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\HomeroomAssignment;
use App\Models\Izin;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Support\Facades\Log;

class InAppNotificationService
{
    /**
     * Send direct notification to a specific user
     */
    public function sendToUser(
        User $user,
        string $title,
        string $message,
        string $category = 'academic',
        ?string $actionUrl = null,
        string $icon = 'bx-bell',
        array $relatedEntity = []
    ): void {
        try {
            $user->notify(new AppNotification(
                title: $title,
                message: $message,
                category: $category,
                actionUrl: $actionUrl,
                icon: $icon,
                relatedEntity: $relatedEntity
            ));
        } catch (\Throwable $e) {
            Log::error("Failed sending in-app notification to user {$user->id}: " . $e->getMessage());
        }
    }

    /**
     * Send broadcast notification to all users with a specific role
     */
    public function sendToRole(
        string $role,
        string $title,
        string $message,
        string $category = 'administrative',
        ?string $actionUrl = null,
        string $icon = 'bx-bell',
        array $relatedEntity = []
    ): void {
        $users = User::where('role', $role)->get();
        foreach ($users as $user) {
            $this->sendToUser($user, $title, $message, $category, $actionUrl, $icon, $relatedEntity);
        }
    }

    /**
     * Route notification when a student submits a leave / sick request (Izin)
     */
    public function notifyLeaveRequested(Izin $izin): void
    {
        $siswa = $izin->siswa;
        if (!$siswa) {
            return;
        }

        $namaSiswa = $siswa->nama ?? 'Siswa';
        $namaKelas = $siswa->kelas->nama_kelas ?? 'Kelas';
        $jenisIzin = strtoupper($izin->jenis ?? 'IZIN');

        $title = "Pengajuan {$jenisIzin} Baru";
        $message = "{$namaSiswa} ({$namaKelas}) mengajukan {$jenisIzin} pada tanggal {$izin->tanggal}.";
        $actionUrl = route('izin.approval');
        $icon = 'bx-envelope';
        $related = ['type' => 'izin', 'id' => $izin->id];

        // 1. Resolve Homeroom Teacher for the student's class
        $homeroom = HomeroomAssignment::where('kelas_id', $siswa->kelas_id)
            ->where('is_active', true)
            ->with('guru.user')
            ->first();

        $notifiedAny = false;
        if ($homeroom && $homeroom->guru && $homeroom->guru->user) {
            $this->sendToUser($homeroom->guru->user, $title, $message, 'leave', $actionUrl, $icon, $related);
            $notifiedAny = true;
        }

        // 2. If no homeroom teacher assigned, notify admins as fallback
        if (!$notifiedAny) {
            $admins = User::whereIn('role', ['admin', 'superadmin'])->get();
            foreach ($admins as $admin) {
                $this->sendToUser($admin, $title, $message, 'leave', $actionUrl, $icon, $related);
            }
        }
    }

    /**
     * Route notification when a leave request is approved or rejected
     */
    public function notifyLeaveStatusUpdated(Izin $izin, string $status, ?string $actorName = null): void
    {
        $siswa = $izin->siswa;
        if (!$siswa) {
            return;
        }

        $jenisIzin = strtoupper($izin->jenis ?? 'IZIN');
        $statusLabel = $status === 'approved' ? 'DISETUJUI' : 'DITOLAK';
        $icon = $status === 'approved' ? 'bx-check-circle' : 'bx-x-circle';
        $byText = $actorName ? " oleh {$actorName}" : '';

        $title = "Pengajuan {$jenisIzin} {$statusLabel}";
        $message = "Pengajuan {$jenisIzin} Anda untuk tanggal {$izin->tanggal} telah {$statusLabel}{$byText}.";
        $actionUrl = route('riwayatkehadiran');
        $related = ['type' => 'izin', 'id' => $izin->id, 'status' => $status];

        // Notify Student user
        if ($siswa->user) {
            $this->sendToUser($siswa->user, $title, $message, 'leave', $actionUrl, $icon, $related);
        }

        // Notify Parent users if connected
        $parents = $siswa->orangtua()->with('user')->get();
        foreach ($parents as $parent) {
            if ($parent->user) {
                $parentMessage = "Pengajuan {$jenisIzin} ananda {$siswa->nama} telah {$statusLabel}{$byText}.";
                $this->sendToUser($parent->user, $title, $parentMessage, 'leave', $actionUrl, $icon, $related);
            }
        }
    }

    /**
     * Route notification when attendance check-in or check-out is recorded
     */
    public function notifyAttendanceRecorded(Absensi $absensi, string $tipe = 'masuk'): void
    {
        $siswa = $absensi->siswa;
        if (!$siswa) {
            return;
        }

        $waktu = $tipe === 'masuk' ? $absensi->jam_masuk : $absensi->jam_pulang;
        $statusStr = $absensi->status === 'terlambat' ? 'TERLAMBAT' : 'TEPAT WAKTU';
        $icon = $absensi->status === 'terlambat' ? 'bx-time-five' : 'bx-fingerprint';

        $title = "Presensi " . ucfirst($tipe) . " Tercatat ({$statusStr})";
        $message = "Presensi {$tipe} berhasil dicatat pada pukul {$waktu} ({$statusStr}).";
        $actionUrl = route('riwayatkehadiran');
        $related = ['type' => 'absensi', 'id' => $absensi->id];

        // Notify student
        if ($siswa->user) {
            $this->sendToUser($siswa->user, $title, $message, 'attendance', $actionUrl, $icon, $related);
        }

        // Notify parents
        $parents = $siswa->orangtua()->with('user')->get();
        foreach ($parents as $parent) {
            if ($parent->user) {
                $parentMessage = "Ananda {$siswa->nama} telah melakukan presensi {$tipe} pada pukul {$waktu} ({$statusStr}).";
                $this->sendToUser($parent->user, $title, $parentMessage, 'attendance', $actionUrl, $icon, $related);
            }
        }
    }
}
