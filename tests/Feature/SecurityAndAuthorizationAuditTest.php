<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use App\Models\Guru;
use App\Models\InventoryBorrowing;
use App\Models\InventoryItem;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\PklDudiPartner;
use App\Models\PklLogbook;
use App\Models\PklPlacement;
use App\Models\Siswa;
use App\Models\SiswaFaceBiometric;
use App\Models\StudentPortfolio;
use App\Models\User;
use App\Services\InAppNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAndAuthorizationAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kaprog;
    private User $guru1;
    private User $guru2;
    private User $siswaUser;
    private Siswa $siswa;
    private Jurusan $jurusan;
    private Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jurusan = Jurusan::create([
            'kode_jurusan' => 'PPLG',
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas'   => '11 PPLG 1',
            'tingkat'      => '11',
            'jurusan_id'   => $this->jurusan->id,
            'tahun_ajaran' => '2026/2027',
            'jumlah_siswa' => 32,
        ]);

        $this->admin = User::create([
            'name'     => 'Administrator',
            'email'    => 'admin.sec@myskanic.test',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $this->kaprog = User::create([
            'name'       => 'Kaprog PPLG',
            'email'      => 'kaprog.pplg@myskanic.test',
            'password'   => bcrypt('password'),
            'role'       => 'kaprog',
            'jurusan_id' => $this->jurusan->id,
        ]);

        $this->guru1 = User::create([
            'name'     => 'Guru Pembimbing Satu',
            'email'    => 'guru1@myskanic.test',
            'password' => bcrypt('password'),
            'role'     => 'guru',
        ]);

        Guru::create([
            'user_id' => $this->guru1->id,
            'nama'    => $this->guru1->name,
            'mapel'   => 'Pemrograman Web',
            'jenis_kelamin' => 'L',
        ]);

        $this->guru2 = User::create([
            'name'     => 'Guru Pembimbing Dua',
            'email'    => 'guru2@myskanic.test',
            'password' => bcrypt('password'),
            'role'     => 'guru',
        ]);

        Guru::create([
            'user_id' => $this->guru2->id,
            'nama'    => $this->guru2->name,
            'mapel'   => 'Basis Data',
            'jenis_kelamin' => 'P',
        ]);

        $this->siswaUser = User::create([
            'name'     => 'Siswa Test',
            'email'    => 'siswa.test@myskanic.test',
            'password' => bcrypt('password'),
            'role'     => 'siswa',
        ]);

        $this->siswa = Siswa::create([
            'user_id'          => $this->siswaUser->id,
            'kelas_id'         => $this->kelas->id,
            'nis'              => '8801',
            'nama'             => 'Siswa Test',
            'jenis_kelamin'    => 'L',
            'biometric_status' => 'enrolled',
        ]);

        SiswaFaceBiometric::create([
            'siswa_id'          => $this->siswa->id,
            'face_descriptor'   => json_encode(array_fill(0, 128, 0.05)),
            'enrollment_status' => 'enrolled',
            'is_active'         => true,
            'quality_score'     => 95,
        ]);
    }

    public function test_student_cannot_create_or_delete_inventory_item(): void
    {
        $item = InventoryItem::create([
            'kode_barang'        => 'TOOL-TEST-01',
            'nama_barang'        => 'Oscilloscope Digital',
            'kategori'           => 'Elektronika',
            'lokasi_penyimpanan' => 'Lab Komputer',
            'total_unit'         => 5,
            'unit_tersedia'      => 5,
            'kondisi'            => 'baik',
            'status'             => 'tersedia',
        ]);

        // Student visits create via web -> redirected to dashboard with error
        $res1 = $this->actingAs($this->siswaUser)->get(route('inventory.create'));
        $res1->assertRedirect(route('dashboard'));
        $res1->assertSessionHas('error');

        // Student attempts store via JSON -> returns 403 Forbidden
        $res2 = $this->actingAs($this->siswaUser)->postJson(route('inventory.store'), [
            'kode_barang'        => 'BOGUS-01',
            'nama_barang'        => 'Hacked Tool',
            'kategori'           => 'Illegal',
            'lokasi_penyimpanan' => 'Secret',
            'total_unit'         => 1,
            'kondisi'            => 'baik',
        ]);
        $res2->assertStatus(403);

        // Student attempts delete via JSON -> returns 403 Forbidden
        $res3 = $this->actingAs($this->siswaUser)->deleteJson(route('inventory.destroy', $item->id));
        $res3->assertStatus(403);
    }

    public function test_student_cannot_approve_tool_loan(): void
    {
        $item = InventoryItem::create([
            'kode_barang'        => 'TOOL-TEST-02',
            'nama_barang'        => 'Multimeter Fluke',
            'kategori'           => 'Alat Ukur',
            'lokasi_penyimpanan' => 'Bengkel Elektro',
            'total_unit'         => 10,
            'unit_tersedia'      => 10,
            'kondisi'            => 'baik',
            'status'             => 'tersedia',
        ]);

        $borrowing = InventoryBorrowing::create([
            'inventory_item_id' => $item->id,
            'user_id'           => $this->siswaUser->id,
            'siswa_id'          => $this->siswa->id,
            'jumlah'            => 1,
            'tanggal_pinjam'    => now()->toDateString(),
            'rencana_kembali'   => now()->addDays(2)->toDateString(),
            'keperluan'         => 'Praktikum Mandiri',
            'status'            => 'pending',
            'kondisi_sebelum'   => 'baik',
        ]);

        $response = $this->actingAs($this->siswaUser)->postJson(route('inventory.borrow.approve', $borrowing->id));
        $response->assertStatus(403);
    }

    public function test_student_cannot_access_pkl_partner_management(): void
    {
        $response = $this->actingAs($this->siswaUser)->get(route('pkl.partners.index'));
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');

        $responsePost = $this->actingAs($this->siswaUser)->postJson(route('pkl.partners.store'), [
            'nama_dudi'       => 'PT Ilegal',
            'bidang_industri' => 'Hacking',
            'alamat'          => 'Unknown',
            'kota'            => 'Unknown',
            'kontak_person'   => 'Tester',
        ]);
        $responsePost->assertStatus(403);
    }

    public function test_teacher_cannot_review_pkl_logbook_of_unassigned_student(): void
    {
        $partner = PklDudiPartner::create([
            'nama_dudi'       => 'PT Inovasi Digital',
            'bidang_industri' => 'Software House',
            'alamat'          => 'Jl. Merdeka No 10',
            'kota'            => 'Bandung',
            'kontak_person'   => 'HRD Tech',
            'status'          => 'aktif',
        ]);

        // Placement assigned to guru1
        $placement = PklPlacement::create([
            'siswa_id'            => $this->siswa->id,
            'pkl_dudi_partner_id' => $partner->id,
            'guru_pembimbing_id'  => $this->guru1->id,
            'tanggal_mulai'       => now()->subMonth()->toDateString(),
            'tanggal_selesai'     => now()->addMonth()->toDateString(),
            'status'              => 'active',
        ]);

        $logbook = PklLogbook::create([
            'pkl_placement_id' => $placement->id,
            'tanggal'          => now()->subDay()->toDateString(),
            'aktivitas'        => 'Pengembangan modul REST API Laravel',
            'status'           => 'submitted',
        ]);

        // Guru 2 (unassigned) attempts to review -> 403 Forbidden
        $response = $this->actingAs($this->guru2)->post(route('pkl.advisor.review', $logbook->id), [
            'status'       => 'approved',
            'catatan_guru' => 'Tampered approval',
        ]);
        $response->assertStatus(403);

        // Guru 1 (assigned advisor) can review
        $responseOk = $this->actingAs($this->guru1)->post(route('pkl.advisor.review', $logbook->id), [
            'status'       => 'approved',
            'catatan_guru' => 'Kerja bagus, lanjutkan.',
        ]);
        $responseOk->assertSessionHas('success');
    }

    public function test_student_cannot_access_discipline_management_or_counseling(): void
    {
        $resDiscipline = $this->actingAs($this->siswaUser)->get(route('discipline.index'));
        $resDiscipline->assertRedirect(route('dashboard'));
        $resDiscipline->assertSessionHas('error');

        $resCounseling = $this->actingAs($this->siswaUser)->get(route('counseling.index'));
        $resCounseling->assertRedirect(route('dashboard'));
        $resCounseling->assertSessionHas('error');

        $resStore = $this->actingAs($this->siswaUser)->postJson(route('discipline.store'), [
            'siswa_id' => $this->siswa->id,
            'judul'    => 'Bogus Infraction',
        ]);
        $resStore->assertStatus(403);
    }

    public function test_student_cannot_curate_portfolios(): void
    {
        $portfolio = StudentPortfolio::create([
            'siswa_id'        => $this->siswa->id,
            'jurusan_id'      => $this->jurusan->id,
            'judul'           => 'Aplikasi Presensi SMK',
            'slug'            => 'aplikasi-presensi-smk',
            'kategori'        => 'Aplikasi Web',
            'deskripsi'       => 'Sistem presensi modern berbasis face biometrics.',
            'status'          => 'submitted',
            'is_tefa_product' => false,
        ]);

        $response = $this->actingAs($this->siswaUser)->get(route('portfolio.moderation'));
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');

        $responseCurate = $this->actingAs($this->siswaUser)->postJson(route('portfolio.moderate', $portfolio->id), [
            'status'          => 'approved',
            'is_tefa_product' => 1,
        ]);
        $responseCurate->assertStatus(403);
    }

    public function test_kaprog_cannot_curate_portfolio_from_different_department(): void
    {
        $otherJurusan = Jurusan::create([
            'kode_jurusan' => 'TJKT',
            'nama_jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi',
        ]);

        $portfolioOther = StudentPortfolio::create([
            'siswa_id'        => $this->siswa->id,
            'jurusan_id'      => $otherJurusan->id, // belongs to TJKT
            'judul'           => 'Konfigurasi MikroTik Border',
            'slug'            => 'konfigurasi-mikrotik-border',
            'kategori'        => 'Jaringan',
            'deskripsi'       => 'Routing OSPF dan firewall multi-WAN.',
            'status'          => 'submitted',
            'is_tefa_product' => false,
        ]);

        // Kaprog PPLG tries to moderate TJKT portfolio
        $response = $this->actingAs($this->kaprog)->post(route('portfolio.moderate', $portfolioOther->id), [
            'status' => 'approved',
        ]);
        $response->assertStatus(403);
    }

    public function test_late_attendance_triggers_terlambat_notification(): void
    {
        $absensi = Absensi::create([
            'siswa_id'             => $this->siswa->id,
            'tanggal'              => now()->toDateString(),
            'jam_masuk'            => '07:25:00',
            'status'               => 'hadir',
            'menit_terlambat'      => 25,
            'keterangan_terlambat' => 'Terlambat 25 menit',
        ]);

        app(InAppNotificationService::class)->notifyAttendanceRecorded($absensi, 'masuk');

        $notif = $this->siswaUser->notifications()->latest()->first();
        $this->assertNotNull($notif);
        $this->assertStringContainsString('TERLAMBAT', $notif->data['title']);
        $this->assertStringContainsString('TERLAMBAT', $notif->data['message']);
        $this->assertEquals('bx-time-five', $notif->data['icon']);
    }

    public function test_kaprog_can_access_discipline_and_pkl_without_sql_errors(): void
    {
        $resDiscipline = $this->actingAs($this->kaprog)->get(route('discipline.index'));
        $resDiscipline->assertStatus(200);

        $resPkl = $this->actingAs($this->kaprog)->get(route('pkl.placements.index'));
        $resPkl->assertStatus(200);
    }

    public function test_teacher_can_access_bk_counseling_without_sql_errors(): void
    {
        $response = $this->actingAs($this->guru1)->get(route('counseling.index'));
        $response->assertStatus(200);
    }
}
