<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\HomeroomAssignment;
use App\Models\Izin;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaFaceBiometric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_leave_submission_notifies_homeroom_teacher()
    {
        // 1. Create Teacher & User
        $teacherUser = User::factory()->create(['role' => 'guru', 'name' => 'Pak Budi']);
        $guru = Guru::create([
            'user_id' => $teacherUser->id,
            'nip'     => '198701012020011001',
            'nama'    => 'Pak Budi',
        ]);

        // 2. Create Kelas & Homeroom Assignment
        $kelas = Kelas::create(['nama_kelas' => '10 PPLG 1', 'jumlah_siswa' => 30]);
        HomeroomAssignment::create([
            'guru_id'     => $guru->id,
            'kelas_id'    => $kelas->id,
            'tahun_ajaran'=> '2026/2027',
            'is_active'   => true,
            'assigned_at' => now(),
        ]);

        // 3. Create Student with enrolled biometric status
        $studentUser = User::factory()->create(['role' => 'siswa', 'name' => 'Fauzi Siswa']);
        $siswa = Siswa::create([
            'user_id'          => $studentUser->id,
            'kelas_id'         => $kelas->id,
            'nis'              => '12345678',
            'nama'             => 'Fauzi Siswa',
            'biometric_status' => 'enrolled',
        ]);
        SiswaFaceBiometric::create([
            'siswa_id'          => $siswa->id,
            'face_descriptor'   => json_encode(array_fill(0, 128, 0.05)),
            'enrollment_status' => 'enrolled',
            'is_active'         => true,
        ]);

        // 4. Student submits leave
        $response = $this->actingAs($studentUser)->post(route('izin.store'), [
            'tanggal'    => now()->toDateString(),
            'jenis'      => 'sakit',
            'keterangan' => 'Sakit demam',
        ]);
        $response->assertSessionHas('success');

        // 5. Homeroom teacher must have received an in-app database notification
        $this->assertEquals(1, $teacherUser->unreadNotifications()->count());
        $notif = $teacherUser->notifications()->first();
        $this->assertEquals('leave', $notif->data['category']);
        $this->assertStringContainsString('Fauzi Siswa', $notif->data['message']);
    }

    public function test_leave_approval_notifies_student()
    {
        $teacherUser = User::factory()->create(['role' => 'guru', 'name' => 'Pak Budi']);
        $guru = Guru::create([
            'user_id' => $teacherUser->id,
            'nip'     => '198701012020011002',
            'nama'    => 'Pak Budi',
        ]);

        $kelas = Kelas::create(['nama_kelas' => '11 Animasi 1', 'jumlah_siswa' => 30]);
        HomeroomAssignment::create([
            'guru_id'     => $guru->id,
            'kelas_id'    => $kelas->id,
            'tahun_ajaran'=> '2026/2027',
            'is_active'   => true,
            'assigned_at' => now(),
        ]);

        $studentUser = User::factory()->create(['role' => 'siswa', 'name' => 'Rina Siswa']);
        $siswa = Siswa::create([
            'user_id'     => $studentUser->id,
            'kelas_id'    => $kelas->id,
            'nis'         => '87654321',
            'nama'        => 'Rina Siswa',
        ]);

        $izin = Izin::create([
            'siswa_id'   => $siswa->id,
            'tanggal'    => now()->toDateString(),
            'jenis'      => 'izin',
            'keterangan' => 'Acara keluarga',
            'status'     => 'pending',
        ]);

        // Homeroom approves
        $this->actingAs($teacherUser)->post(route('izin.approve', $izin->id), [
            'catatan' => 'Disetujui silakan',
        ]);

        // Student must have received approval notification
        $this->assertEquals(1, $studentUser->unreadNotifications()->count());
        $notif = $studentUser->notifications()->first();
        $this->assertEquals('leave', $notif->data['category']);
        $this->assertStringContainsString('DISETUJUI', $notif->data['title']);
    }

    public function test_unread_count_api_and_mark_as_read_workflow()
    {
        $user = User::factory()->create(['role' => 'admin']);

        // Send a notification
        app(\App\Services\InAppNotificationService::class)->sendToUser(
            $user,
            'Pengumuman Akademik',
            'Jadwal ujian semester telah diterbitkan.',
            'academic',
            route('dashboard')
        );

        $this->actingAs($user);

        // Check unread count API
        $response = $this->getJson(route('notifications.unread'));
        $response->assertOk();
        $response->assertJsonPath('unread_count', 1);

        $notif = $user->unreadNotifications()->first();

        // Mark as read
        $markResponse = $this->postJson(route('notifications.mark_read', $notif->id));
        $markResponse->assertOk();
        $this->assertEquals(0, $user->unreadNotifications()->count());

        // Check unread count API again
        $response2 = $this->getJson(route('notifications.unread'));
        $response2->assertJsonPath('unread_count', 0);
    }

    public function test_user_cannot_mark_another_users_notification()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        app(\App\Services\InAppNotificationService::class)->sendToUser(
            $userA,
            'Pribadi',
            'Notifikasi pribadi user A',
            'administrative'
        );

        $notifA = $userA->notifications()->first();

        // User B tries to mark User A's notification as read
        $this->actingAs($userB);
        $response = $this->post(route('notifications.mark_read', $notifA->id));
        $response->assertNotFound();
    }
}
