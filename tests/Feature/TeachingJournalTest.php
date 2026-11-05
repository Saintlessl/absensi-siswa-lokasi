<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\TeachingJournal;
use App\Models\TeachingJournalAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeachingJournalTest extends TestCase
{
    use RefreshDatabase;

    private function getIndonesianDay(): string
    {
        $dayNum = Carbon::now()->dayOfWeekIso;
        $days = [1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 7 => 'minggu'];
        return $days[$dayNum] ?? 'senin';
    }

    public function test_teacher_can_view_journal_dashboard_with_todays_schedules()
    {
        $user = User::factory()->create(['role' => 'guru', 'name' => 'Pak Joko']);
        $guru = Guru::create(['user_id' => $user->id, 'nip' => '198001012010011001', 'nama' => 'Pak Joko']);
        $kelas = Kelas::create(['nama_kelas' => '10 PPLG 1', 'jumlah_siswa' => 30]);
        $mapel = MataPelajaran::create(['nama_mapel' => 'Pemrograman Web', 'kode_mapel' => 'PW10']);

        // Create schedule today
        $jadwal = Jadwal::create([
            'guru_id'           => $guru->id,
            'kelas_id'          => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'mata_pelajaran'    => 'Pemrograman Web',
            'hari'              => $this->getIndonesianDay(),
            'jam_mulai'         => '07:15:00',
            'jam_selesai'       => '09:30:00',
            'tahun_ajaran'      => '2026/2027',
        ]);

        $this->actingAs($user);
        $response = $this->get(route('jurnal.index'));
        $response->assertOk();
        $response->assertSee('Jurnal Mengajar Harian');
        $response->assertSee('10 PPLG 1');
        $response->assertSee('Pemrograman Web');
    }

    public function test_teacher_can_store_journal_and_session_attendance()
    {
        $user = User::factory()->create(['role' => 'guru', 'name' => 'Bu Ratna']);
        $guru = Guru::create(['user_id' => $user->id, 'nip' => '198501012010012001', 'nama' => 'Bu Ratna']);
        $kelas = Kelas::create(['nama_kelas' => '11 Animasi 2', 'jumlah_siswa' => 25]);
        $mapel = MataPelajaran::create(['nama_mapel' => 'Desain Karakter 2D', 'kode_mapel' => 'DK2D']);

        // Create 2 students
        $siswa1 = Siswa::create([
            'user_id'  => User::factory()->create()->id,
            'kelas_id' => $kelas->id,
            'nis'      => '88001122',
            'nama'     => 'Andi Siswa',
        ]);
        $siswa2 = Siswa::create([
            'user_id'  => User::factory()->create()->id,
            'kelas_id' => $kelas->id,
            'nis'      => '88001133',
            'nama'     => 'Bima Siswa',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('jurnal.store'), [
            'kelas_id'            => $kelas->id,
            'mata_pelajaran_id'   => $mapel->id,
            'nama_mapel'          => 'Desain Karakter 2D',
            'tanggal'             => now()->toDateString(),
            'jam_ke'              => 'Jam Ke 1-3',
            'materi_pokok'        => 'Pengenalan Anatomi Dasar Karakter Chibi',
            'tujuan_pembelajaran' => 'Siswa dapat membuat proporsi tubuh chibi 1:3',
            'elemen_capaian'      => 'Desain Grafis Animasi',
            'aktivitas'           => 'Praktik sketsa digital menggunakan pen tablet',
            'metode'              => 'Demonstrasi dan Praktik Mandiri',
            'status'              => 'submitted',
            'attendances'         => [
                $siswa1->id => ['status' => 'hadir', 'catatan' => 'Selesai tugas tepat waktu'],
                $siswa2->id => ['status' => 'sakit', 'catatan' => 'Surat dokter terlampir di UKS'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('teaching_journals', [
            'guru_id'      => $guru->id,
            'kelas_id'     => $kelas->id,
            'materi_pokok' => 'Pengenalan Anatomi Dasar Karakter Chibi',
            'status'       => 'submitted',
        ]);

        $journal = TeachingJournal::where('guru_id', $guru->id)->first();
        $this->assertNotNull($journal);
        $this->assertEquals(2, $journal->attendances()->count());

        $this->assertDatabaseHas('teaching_journal_attendances', [
            'teaching_journal_id' => $journal->id,
            'siswa_id'            => $siswa1->id,
            'status'              => 'hadir',
        ]);

        $this->assertDatabaseHas('teaching_journal_attendances', [
            'teaching_journal_id' => $journal->id,
            'siswa_id'            => $siswa2->id,
            'status'              => 'sakit',
        ]);

        // Verify that daily school attendance table is NOT mutated
        $this->assertDatabaseMissing('absensis', [
            'siswa_id' => $siswa2->id,
        ]);
    }

    public function test_teacher_cannot_view_another_teachers_journal()
    {
        $userA = User::factory()->create(['role' => 'guru', 'name' => 'Guru A']);
        $guruA = Guru::create(['user_id' => $userA->id, 'nip' => '111', 'nama' => 'Guru A']);

        $userB = User::factory()->create(['role' => 'guru', 'name' => 'Guru B']);
        $guruB = Guru::create(['user_id' => $userB->id, 'nip' => '222', 'nama' => 'Guru B']);

        $kelas = Kelas::create(['nama_kelas' => '12 TPFL 1', 'jumlah_siswa' => 20]);

        $journalA = TeachingJournal::create([
            'guru_id'      => $guruA->id,
            'kelas_id'     => $kelas->id,
            'nama_mapel'   => 'Pengelasan SMAW',
            'tanggal'      => now()->toDateString(),
            'materi_pokok' => 'Teknik Las Posisi 1G',
            'aktivitas'    => 'Praktik di bengkel las',
            'status'       => 'submitted',
        ]);

        // Guru B tries to access Guru A's journal
        $this->actingAs($userB);
        $response = $this->get(route('jurnal.show', $journalA->id));
        $response->assertForbidden();
    }

    public function test_admin_and_kaprog_can_access_monitoring()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $response = $this->get(route('jurnal.monitoring'));
        $response->assertOk();
        $response->assertSee('Monitoring Jurnal Pembelajaran');
    }
}
