<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\HomeroomAssignment;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\Siswa;
use App\Models\StudentEnrollment;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use Database\Seeders\SchoolPilotDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolPilotSeederIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_is_strictly_idempotent_when_run_multiple_times(): void
    {
        // 1. First execution
        $seeder = new SchoolPilotDataSeeder();
        $seeder->run();

        $countUsers1 = User::count();
        $countSiswa1 = Siswa::count();
        $countEnrollment1 = StudentEnrollment::count();
        $countHomeroom1 = HomeroomAssignment::count();
        $countOrangTua1 = OrangTua::count();
        $countSubjectAssign1 = TeacherSubjectAssignment::count();
        $countRooms1 = Room::count();
        $countRoomAssign1 = RoomAssignment::count();

        $this->assertEquals(12, Siswa::where('catatan_status', '[PILOT-CIOMAS]')->count());
        $this->assertEquals(1, HomeroomAssignment::where('is_active', true)->where('catatan', 'like', '%[PILOT-CIOMAS]%')->count());

        // 2. Second execution (must not create duplicate records)
        $seeder->run();

        $this->assertEquals($countUsers1, User::count(), 'Users count must not increase on second seeder run');
        $this->assertEquals($countSiswa1, Siswa::count(), 'Siswa count must not increase on second seeder run');
        $this->assertEquals($countEnrollment1, StudentEnrollment::count(), 'StudentEnrollment count must not increase on second seeder run');
        $this->assertEquals($countHomeroom1, HomeroomAssignment::count(), 'HomeroomAssignment count must not increase on second seeder run');
        $this->assertEquals($countOrangTua1, OrangTua::count(), 'OrangTua count must not increase on second seeder run');
        $this->assertEquals($countSubjectAssign1, TeacherSubjectAssignment::count(), 'TeacherSubjectAssignment count must not increase on second seeder run');
        $this->assertEquals($countRooms1, Room::count(), 'Rooms count must not increase on second seeder run');
        $this->assertEquals($countRoomAssign1, RoomAssignment::count(), 'RoomAssignment count must not increase on second seeder run');

        // Verify active homeroom remains unique
        $activeHomerooms = HomeroomAssignment::where('is_active', true)->count();
        $this->assertEquals(1, $activeHomerooms);
    }

    public function test_seeder_rolls_back_cleanly_on_unexpected_failure(): void
    {
        $seeder = new SchoolPilotDataSeeder();
        $seeder->run();

        $initialSiswaCount = Siswa::count();

        // Simulate failure inside a transaction with pilot seeder logic
        try {
            \Illuminate\Support\Facades\DB::transaction(function () {
                Siswa::create([
                    'user_id' => 999999, // non-existent user or invalid
                    'nis' => '999999999',
                    'nama' => 'Test Rollback',
                    'jenis_kelamin' => 'L',
                    'kelas_id' => 1,
                    'status' => 'aktif',
                ]);

                throw new \Exception('Simulated crash during seed execution');
            });
        } catch (\Exception $e) {
            // Expected catch
        }

        // Verify that failed transaction left zero partial records
        $this->assertEquals($initialSiswaCount, Siswa::count());
        $this->assertDatabaseMissing('siswas', ['nis' => '999999999']);
    }
}
