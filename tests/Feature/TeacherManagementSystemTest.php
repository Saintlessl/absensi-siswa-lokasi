<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\HomeroomAssignment;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Room;
use App\Models\Siswa;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use App\Services\TeacherAccountService;
use App\Services\TeacherAssignmentService;
use App\Services\TeacherScheduleService;
use App\Services\TeacherWorkloadService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TeacherManagementSystemTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superadmin;
    protected Jurusan $pplg;
    protected Kelas $kelas10;
    protected Kelas $kelas11;
    protected MataPelajaran $mapelCoding;
    protected Room $roomLab;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::firstOrCreate(
            ['email' => 'admin.test.tms@skanic.com'],
            ['name' => 'Super Admin TMS Test', 'password' => bcrypt('password123'), 'role' => 'superadmin', 'status' => 'active']
        );

        $this->pplg = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'PPLG_TEST'],
            ['nama_jurusan' => 'PPLG Program Test', 'is_active' => true]
        );

        $this->kelas10 = Kelas::firstOrCreate(
            ['nama_kelas' => '10 PPLG Test 1'],
            ['jurusan_id' => $this->pplg->id, 'tingkat' => '10', 'nomor_rombel' => '1', 'tahun_ajaran' => '2026/2027', 'qr_token' => 'qr-test-k10', 'jumlah_siswa' => 36]
        );

        $this->kelas11 = Kelas::firstOrCreate(
            ['nama_kelas' => '11 PPLG Test 1'],
            ['jurusan_id' => $this->pplg->id, 'tingkat' => '11', 'nomor_rombel' => '1', 'tahun_ajaran' => '2026/2027', 'qr_token' => 'qr-test-k11', 'jumlah_siswa' => 36]
        );

        $this->mapelCoding = MataPelajaran::firstOrCreate(
            ['kode_mapel' => 'PPLG-TEST-01'],
            ['nama_mapel' => 'Pemrograman Web Test', 'kelompok' => 'Kejuruan', 'is_active' => true]
        );

        $this->roomLab = Room::firstOrCreate(
            ['code' => 'LAB-TEST-01'],
            ['name' => 'Lab Komputer Test 1', 'capacity' => 36, 'building' => 'Gedung B', 'is_active' => true, 'qr_token' => 'qr-lab-01']
        );
    }

    public function test_superadmin_can_access_teacher_directory_and_workload()
    {
        $response = $this->actingAs($this->superadmin)->get(route('guru'));
        $response->assertStatus(200);
        $response->assertSee('Manajemen Data Guru');

        $responseWorkload = $this->actingAs($this->superadmin)->get(route('guru.workload'));
        $responseWorkload->assertStatus(200);
        $responseWorkload->assertSee('Manajemen Beban Kerja Mengajar');
    }

    public function test_create_teacher_with_direct_password_and_activation_link()
    {
        $accountService = app(TeacherAccountService::class);

        // 1. Direct password creation
        $resDirect = $accountService->createTeacher([
            'nama'               => 'Budi Santoso Test S.Pd',
            'nip'                => '198701012010011001_TEST',
            'email'              => 'budi.test@skanic.com',
            'jenis_kelamin'      => 'L',
            'telepon'            => '081234567891',
            'status_kepegawaian' => 'PNS',
        ], 'SecretPassword123!', false);

        $this->assertInstanceOf(Guru::class, $resDirect['guru']);
        $this->assertEquals('active', $resDirect['user']->status);
        $this->assertNull($resDirect['activation_url']);

        // 2. Invitation link creation
        $resInvite = $accountService->createTeacher([
            'nama'               => 'Siti Rahma Test M.Kom',
            'email'              => 'siti.test@skanic.com',
            'jenis_kelamin'      => 'P',
            'status_kepegawaian' => 'PPPK',
        ], null, true);

        $this->assertEquals('pending', $resInvite['user']->status);
        $this->assertNotNull($resInvite['activation_url']);
        $this->assertDatabaseHas('teacher_account_activations', [
            'user_id' => $resInvite['user']->id,
        ]);
    }

    public function test_teacher_activation_link_flow()
    {
        $accountService = app(TeacherAccountService::class);
        $res = $accountService->createTeacher([
            'nama'          => 'Ahmad Fauzi Test',
            'email'         => 'ahmad.fauzi@skanic.com',
            'jenis_kelamin' => 'L',
        ], null, true);

        $user = $res['user'];
        $this->assertEquals('pending', $user->status);

        // Parse token from generated URL
        preg_match('#/aktivasi-guru/([a-zA-Z0-9]+)#', $res['activation_url'], $matches);
        $token = $matches[1];

        // Access activation page
        $pageResponse = $this->get("/aktivasi-guru/{$token}");
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('Aktivasi Akun Guru');

        // Submit activation form
        $postResponse = $this->post("/aktivasi-guru/{$token}", [
            'password'              => 'MyNewSecurePassword!2026',
            'password_confirmation' => 'MyNewSecurePassword!2026',
        ]);

        $postResponse->assertRedirect(route('dashboard'));
        $this->assertEquals('active', $user->fresh()->status);
    }

    public function test_teacher_subject_and_homeroom_assignments_with_history()
    {
        $accountService = app(TeacherAccountService::class);
        $assignmentService = app(TeacherAssignmentService::class);

        $t1 = $accountService->createTeacher([
            'nama'          => 'Guru Penugasan 1',
            'email'         => 'guru.penugasan1@skanic.com',
            'jenis_kelamin' => 'L',
        ], 'Pass12345!', false);

        $t2 = $accountService->createTeacher([
            'nama'          => 'Guru Penugasan 2',
            'email'         => 'guru.penugasan2@skanic.com',
            'jenis_kelamin' => 'P',
        ], 'Pass12345!', false);

        $guru1 = $t1['guru'];
        $guru2 = $t2['guru'];

        // Assign subject to Guru 1 on 2 classes
        $sub1 = $assignmentService->assignSubject(
            $guru1->id,
            $this->mapelCoding->id,
            $this->kelas10->id,
            '2026/2027'
        );
        $sub2 = $assignmentService->assignSubject(
            $guru1->id,
            $this->mapelCoding->id,
            $this->kelas11->id,
            '2026/2027'
        );
        $this->assertInstanceOf(TeacherSubjectAssignment::class, $sub1);
        $this->assertEquals(2, $guru1->fresh()->activeSubjectAssignments->count());

        // Assign Guru 1 as Homeroom for Kelas 10
        $hr1 = $assignmentService->assignHomeroom($guru1->id, $this->kelas10->id, '2026/2027', 'SK No. 01');
        $this->assertTrue($hr1->is_active);
        $this->assertEquals($guru1->nama, $this->kelas10->fresh()->wali_kelas);

        // Reassign Kelas 10 Homeroom to Guru 2:
        // System must deactivate Guru 1's homeroom record, create new active for Guru 2, and update Kelas wali_kelas
        $hr2 = $assignmentService->assignHomeroom($guru2->id, $this->kelas10->id, '2026/2027', 'SK No. 02 Pergantian');

        $this->assertFalse($hr1->fresh()->is_active);
        $this->assertTrue($hr2->is_active);
        $this->assertEquals($guru2->nama, $this->kelas10->fresh()->wali_kelas);

        // Assign Guru 1 as KAPROG PPLG with historical record
        $kaprogAssignment = $assignmentService->assignKaprog($guru1->id, $this->pplg->id, '2026/2027', 'SK KAPROG PPLG');
        $this->assertTrue($guru1->fresh()->is_kaprog);
        $this->assertEquals($this->pplg->id, $guru1->user->fresh()->jurusan_id);
        $this->assertDatabaseHas('kaprog_assignments', [
            'guru_id'    => $guru1->id,
            'jurusan_id' => $this->pplg->id,
            'is_active'  => true,
        ]);
    }

    public function test_store_teacher_with_mandatory_subject_and_additional_duties_workflow()
    {
        $response = $this->actingAs($this->superadmin)->post(route('guru.store'), [
            'nama'               => 'Guru Multi Peran Test',
            'nip'                => '199001012020011005',
            'email'              => 'guru.multiperan@skanic.com',
            'jenis_kelamin'      => 'L',
            'status_kepegawaian' => 'PNS',
            'password_mode'      => 'direct',
            'password'           => 'PasswordAdmin123!',
            // Mandatory subject and multiple classes
            'mata_pelajaran_id'  => $this->mapelCoding->id,
            'kelas_ids'          => [$this->kelas10->id, $this->kelas11->id],
            'tahun_ajaran'       => '2026/2027',
            // Additional Duties: Homeroom + KAPROG simultaneously
            'is_homeroom'        => '1',
            'homeroom_kelas_id'  => $this->kelas10->id,
            'is_kaprog'          => '1',
            'kaprog_jurusan_id'  => $this->pplg->id,
        ]);

        $user = User::where('email', 'guru.multiperan@skanic.com')->first();
        $this->assertNotNull($user);
        $guru = $user->guru;
        $this->assertNotNull($guru);
        $response->assertRedirect(route('guru.show', $guru->id));

        // Verifications:
        // 1. Mandatory subject assigned to both classes
        $this->assertEquals(2, $guru->activeSubjectAssignments()->count());

        // 2. Additional duty: Homeroom is active
        $this->assertTrue($guru->is_homeroom);
        $this->assertEquals($this->kelas10->id, $guru->activeHomeroomAssignment->kelas_id);
        $this->assertEquals($guru->nama, $this->kelas10->fresh()->wali_kelas);

        // 3. Additional duty: KAPROG is active
        $this->assertTrue($guru->is_kaprog);
        $this->assertEquals($this->pplg->id, $guru->user->jurusan_id);
        $this->assertEquals($this->pplg->id, $guru->activeKaprogAssignment->jurusan_id);
    }

    public function test_cannot_remove_last_subject_assignment_of_active_teacher()
    {
        $accountService = app(TeacherAccountService::class);
        $assignmentService = app(TeacherAssignmentService::class);

        $t = $accountService->createTeacher([
            'nama'          => 'Guru Tunggal Mapel Test',
            'email'         => 'guru.tunggal@skanic.com',
            'jenis_kelamin' => 'P',
        ], 'Pass12345!', false);
        $guru = $t['guru'];

        $sub = $assignmentService->assignSubject($guru->id, $this->mapelCoding->id, $this->kelas10->id, '2026/2027');

        // Trying to delete the only active subject assignment must throw ValidationException
        $this->expectException(ValidationException::class);
        $assignmentService->removeSubjectAssignment($sub->id);
    }

    public function test_schedule_conflict_validator_prevents_overlap()
    {
        $accountService = app(TeacherAccountService::class);
        $scheduleService = app(TeacherScheduleService::class);

        $t = $accountService->createTeacher([
            'nama'          => 'Guru Jadwal Test',
            'email'         => 'guru.jadwal@skanic.com',
            'jenis_kelamin' => 'L',
        ], 'Pass12345!', false);
        $guru = $t['guru'];

        // Create base schedule: Senin 07:00 - 08:30 on Kelas 10 in Lab 1
        $j1 = $scheduleService->createSchedule([
            'guru_id'           => $guru->id,
            'mata_pelajaran_id' => $this->mapelCoding->id,
            'kelas_id'          => $this->kelas10->id,
            'hari'              => 'Senin',
            'jam_mulai'         => '07:00',
            'jam_selesai'       => '08:30',
            'tahun_ajaran'      => '2026/2027',
            'room_id'           => $this->roomLab->id,
        ]);
        $this->assertInstanceOf(Jadwal::class, $j1);

        // Conflict 1: Same Teacher overlapping time on another class (Senin 08:00 - 09:30)
        $this->expectException(ValidationException::class);
        $scheduleService->assertNoConflict([
            'guru_id'      => $guru->id,
            'kelas_id'     => $this->kelas11->id,
            'hari'         => 'Senin',
            'jam_mulai'    => '08:00',
            'jam_selesai'  => '09:30',
            'tahun_ajaran' => '2026/2027',
        ]);
    }

    public function test_teacher_workload_calculation_and_optimal_status()
    {
        $accountService = app(TeacherAccountService::class);
        $scheduleService = app(TeacherScheduleService::class);
        $workloadService = app(TeacherWorkloadService::class);

        $t = $accountService->createTeacher([
            'nama'          => 'Guru Workload Test',
            'email'         => 'guru.workload@skanic.com',
            'jenis_kelamin' => 'L',
        ], 'Pass12345!', false);
        $guru = $t['guru'];

        // Add 1 session of 90 minutes (2 JP of 45 mins)
        $scheduleService->createSchedule([
            'guru_id'           => $guru->id,
            'mata_pelajaran_id' => $this->mapelCoding->id,
            'kelas_id'          => $this->kelas10->id,
            'hari'              => 'Selasa',
            'jam_mulai'         => '07:00',
            'jam_selesai'       => '08:30',
            'tahun_ajaran'      => '2026/2027',
        ]);

        $workload = $workloadService->calculateWorkload($guru, '2026/2027');

        $this->assertEquals(2.0, $workload['total_jp']);
        $this->assertEquals(90, $workload['total_minutes']);
        $this->assertEquals('underload', $workload['status_category']); // < 24 JP is underload
    }

    public function test_teacher_scoped_class_access_security()
    {
        $accountService = app(TeacherAccountService::class);
        $assignmentService = app(TeacherAssignmentService::class);

        $t = $accountService->createTeacher([
            'nama'          => 'Guru Scoped Test',
            'email'         => 'guru.scoped@skanic.com',
            'jenis_kelamin' => 'L',
        ], 'Pass12345!', false);
        $teacherUser = $t['user'];
        $guru = $t['guru'];

        // Assign only Kelas 10
        $assignmentService->assignSubject($guru->id, $this->mapelCoding->id, $this->kelas10->id, '2026/2027');

        // Teacher accesses assigned class: Allowed 200
        $responseAssigned = $this->actingAs($teacherUser)->get(route('teacher.class.students', $this->kelas10->id));
        $responseAssigned->assertStatus(200);

        // Teacher accesses unassigned class: Forbidden 403
        $responseUnassigned = $this->actingAs($teacherUser)->get(route('teacher.class.students', $this->kelas11->id));
        $responseUnassigned->assertStatus(403);
    }

    public function test_suspended_account_cannot_login()
    {
        $accountService = app(TeacherAccountService::class);
        $t = $accountService->createTeacher([
            'nama'          => 'Guru Suspended Test',
            'email'         => 'guru.suspended@skanic.com',
            'jenis_kelamin' => 'L',
        ], 'Password123!', false);

        $accountService->setAccountStatus($t['user'], 'suspended');

        $response = $this->post(route('proseslogin'), [
            'email'    => 'guru.suspended@skanic.com',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }
}
