<?php

namespace Tests\Feature;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\PklAttendance;
use App\Models\PklDudiPartner;
use App\Models\PklLogbook;
use App\Models\PklPlacement;
use App\Models\Siswa;
use App\Models\SiswaFaceBiometric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PklManagementTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $guruPembimbing;
    private $jurusan;
    private $kelas;
    private $siswaUser;
    private $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jurusan = Jurusan::create([
            'kode_jurusan' => 'PPLG',
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas'   => '12 PPLG 1',
            'tingkat'      => '12',
            'jurusan_id'   => $this->jurusan->id,
            'tahun_ajaran' => '2026/2027',
            'jumlah_siswa' => 30,
        ]);

        $this->admin = User::create([
            'name'     => 'Hubin Administrator',
            'email'    => 'hubin@smk.test',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $this->guruPembimbing = User::create([
            'name'     => 'Pak Budi Pembimbing',
            'email'    => 'budi.pkl@smk.test',
            'password' => bcrypt('password'),
            'role'     => 'guru',
        ]);

        $this->siswaUser = User::create([
            'name'     => 'Fajar Magang',
            'email'    => 'fajar.pkl@smk.test',
            'password' => bcrypt('password'),
            'role'     => 'siswa',
        ]);

        $this->siswa = Siswa::create([
            'user_id'          => $this->siswaUser->id,
            'nama'             => 'Fajar Magang',
            'nis'              => '10293848',
            'nisn'             => '0081234568',
            'kelas_id'         => $this->kelas->id,
            'jurusan_id'       => $this->jurusan->id,
            'biometric_status' => 'enrolled',
        ]);

        SiswaFaceBiometric::create([
            'siswa_id'          => $this->siswa->id,
            'face_descriptor'   => json_encode(array_fill(0, 128, 0.05)),
            'enrollment_status' => 'enrolled',
            'is_active'         => true,
            'enrolled_at'       => now(),
        ]);
    }

    public function test_admin_can_register_dudi_partner_with_geofence()
    {
        $response = $this->actingAs($this->admin)->post(route('pkl.partners.store'), [
            'nama_dudi'       => 'PT Agate International',
            'bidang_industri' => 'Game Development & Animation',
            'alamat'          => 'Jl. Sumur Bandung No. 12',
            'kota'            => 'Bandung',
            'kontak_person'   => 'Ibu Maya (HR)',
            'telepon'         => '08122334455',
            'pembimbing_dudi' => 'Mas Dimas (Lead Dev)',
            'latitude'        => -6.890500,
            'longitude'       => 107.610500,
            'radius_meter'    => 200,
            'jurusan_id'      => $this->jurusan->id,
        ]);

        $response->assertRedirect(route('pkl.partners.index'));
        $this->assertDatabaseHas('pkl_dudi_partners', [
            'nama_dudi'    => 'PT Agate International',
            'radius_meter' => 200,
            'status'       => 'aktif',
        ]);
    }

    public function test_admin_can_place_student_to_partner()
    {
        $partner = PklDudiPartner::create([
            'nama_dudi'       => 'PT Gameloft Indonesia',
            'bidang_industri' => 'Game Studio',
            'alamat'          => 'Yogyakarta',
            'kota'            => 'Yogyakarta',
            'kontak_person'   => 'HR Gameloft',
            'latitude'        => -7.780000,
            'longitude'       => 110.370000,
            'radius_meter'    => 150,
            'status'          => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->post(route('pkl.placements.store'), [
            'siswa_id'            => $this->siswa->id,
            'pkl_dudi_partner_id' => $partner->id,
            'guru_pembimbing_id'  => $this->guruPembimbing->id,
            'tanggal_mulai'       => now()->toDateString(),
            'tanggal_selesai'     => now()->addMonths(6)->toDateString(),
            'catatan'             => 'Divisi 3D Game Art',
        ]);

        $response->assertRedirect(route('pkl.placements.index'));
        $this->assertDatabaseHas('pkl_placements', [
            'siswa_id'            => $this->siswa->id,
            'pkl_dudi_partner_id' => $partner->id,
            'guru_pembimbing_id'  => $this->guruPembimbing->id,
            'status'              => 'active',
        ]);
    }

    public function test_student_cannot_have_duplicate_active_placements()
    {
        $partner = PklDudiPartner::create([
            'nama_dudi'       => 'PT Telkom Bandung',
            'bidang_industri' => 'Telekomunikasi',
            'alamat'          => 'Bandung',
            'kota'            => 'Bandung',
            'kontak_person'   => 'Pak Hendra',
            'status'          => 'aktif',
        ]);

        PklPlacement::create([
            'siswa_id'            => $this->siswa->id,
            'pkl_dudi_partner_id' => $partner->id,
            'guru_pembimbing_id'  => $this->guruPembimbing->id,
            'tanggal_mulai'       => now()->toDateString(),
            'tanggal_selesai'     => now()->addMonths(3)->toDateString(),
            'status'              => 'active',
        ]);

        $response = $this->actingAs($this->admin)->post(route('pkl.placements.store'), [
            'siswa_id'            => $this->siswa->id,
            'pkl_dudi_partner_id' => $partner->id,
            'tanggal_mulai'       => now()->toDateString(),
            'tanggal_selesai'     => now()->addMonths(3)->toDateString(),
        ]);

        $response->assertSessionHas('error');
    }

    public function test_student_geofence_checkin_validates_radius()
    {
        $partner = PklDudiPartner::create([
            'nama_dudi'       => 'PT Inovasi Digital',
            'bidang_industri' => 'Tech',
            'alamat'          => 'Cimahi',
            'kota'            => 'Cimahi',
            'kontak_person'   => 'Budi',
            'latitude'        => -6.872000,
            'longitude'       => 107.542000,
            'radius_meter'    => 100,
            'status'          => 'aktif',
        ]);

        $placement = PklPlacement::create([
            'siswa_id'            => $this->siswa->id,
            'pkl_dudi_partner_id' => $partner->id,
            'tanggal_mulai'       => now()->subDays(5)->toDateString(),
            'tanggal_selesai'     => now()->addMonths(3)->toDateString(),
            'status'              => 'active',
        ]);

        // Scenario 1: Inside radius (almost identical coordinates)
        $response = $this->actingAs($this->siswaUser)->post(route('pkl.student.checkin'), [
            'latitude'  => -6.872010,
            'longitude' => 107.542010,
            'catatan'   => 'Hadir tepat waktu di kantor',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pkl_attendances', [
            'pkl_placement_id'  => $placement->id,
            'tanggal'           => now()->toDateString(),
            'is_location_valid' => true,
        ]);
    }

    public function test_student_can_submit_logbook_and_advisor_can_review()
    {
        $partner = PklDudiPartner::create([
            'nama_dudi'       => 'Studio Animasi 3D',
            'bidang_industri' => 'Multimedia',
            'alamat'          => 'Bandung',
            'kota'            => 'Bandung',
            'kontak_person'   => 'Rian',
            'status'          => 'aktif',
        ]);

        $placement = PklPlacement::create([
            'siswa_id'            => $this->siswa->id,
            'pkl_dudi_partner_id' => $partner->id,
            'guru_pembimbing_id'  => $this->guruPembimbing->id,
            'tanggal_mulai'       => now()->subDays(10)->toDateString(),
            'tanggal_selesai'     => now()->addMonths(2)->toDateString(),
            'status'              => 'active',
        ]);

        // Student submits logbook
        $response = $this->actingAs($this->siswaUser)->post(route('pkl.student.logbook'), [
            'tanggal'               => now()->toDateString(),
            'aktivitas'             => 'Membuat 3D model environment sci-fi di Blender dan texturing Substance Painter.',
            'kompetensi_dipelajari' => 'Hard surface 3D Modeling & PBR texturing',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pkl_logbooks', [
            'pkl_placement_id'      => $placement->id,
            'tanggal'               => now()->toDateString(),
            'kompetensi_dipelajari' => 'Hard surface 3D Modeling & PBR texturing',
            'status'                => 'submitted',
        ]);

        $logbook = PklLogbook::where('pkl_placement_id', $placement->id)->first();

        // Advisor reviews & approves logbook
        $reviewResponse = $this->actingAs($this->guruPembimbing)->post(route('pkl.advisor.review', $logbook->id), [
            'status'       => 'approved',
            'catatan_guru' => 'Bagus sekali, pertahankan topology quad mesh yang bersih.',
        ]);

        $reviewResponse->assertSessionHas('success');
        $logbook->refresh();

        $this->assertEquals('approved', $logbook->status);
        $this->assertEquals($this->guruPembimbing->id, $logbook->verified_by);
        $this->assertNotNull($logbook->verified_at);
    }
}
