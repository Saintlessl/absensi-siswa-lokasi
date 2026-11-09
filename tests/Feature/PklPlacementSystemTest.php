<?php

namespace Tests\Feature;

use App\Models\Angkatan;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\PklDudiPartner;
use App\Models\PklPlacement;
use App\Models\Siswa;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PKL system: mitra CRUD, dependent dropdown Jurusan -> Rombel 12 -> Siswa,
 * backend relation validation and KAPROG department scoping.
 */
class PklPlacementSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $pplg;
    protected $anim;
    protected $rombel12Pplg;
    protected $rombel11Pplg;
    protected $rombel12Anim;
    protected $kaprogPplg;
    protected $admin;
    protected $siswaX;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pplg = Jurusan::create(['kode_jurusan' => 'PPLG', 'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim', 'is_active' => true]);
        $this->anim = Jurusan::create(['kode_jurusan' => 'ANIM', 'nama_jurusan' => 'Animasi', 'is_active' => true]);

        $ta = \App\Models\TahunAjaran::create([
            'nama' => '2026/2027 Ganjil', 'tahun_mulai' => 2026, 'tahun_selesai' => 2027,
            'is_active' => true, 'status' => 'aktif',
            'tanggal_mulai' => '2026-07-15', 'tanggal_selesai' => '2026-12-20',
        ]);

        $this->rombel12Pplg = Kelas::create(['nama_kelas' => 'XII PPLG 1', 'jurusan_id' => $this->pplg->id, 'tingkat' => 12, 'nomor_rombel' => 1, 'tahun_ajaran' => '2026/2027', 'is_active' => true]);
        $this->rombel11Pplg = Kelas::create(['nama_kelas' => 'XI PPLG 1', 'jurusan_id' => $this->pplg->id, 'tingkat' => 11, 'nomor_rombel' => 1, 'tahun_ajaran' => '2026/2027', 'is_active' => true]);
        $this->rombel12Anim = Kelas::create(['nama_kelas' => 'XII ANIM 1', 'jurusan_id' => $this->anim->id, 'tingkat' => 12, 'nomor_rombel' => 1, 'tahun_ajaran' => '2026/2027', 'is_active' => true]);

        $this->admin = User::create(['name' => 'Admin Uji', 'email' => 'admin-uji@test.local', 'password' => bcrypt('password'), 'role' => 'admin', 'is_active' => 1]);

        $kaprogUser = User::create(['name' => 'Kaprog PPLG', 'email' => 'kaprog-pplg@test.local', 'password' => bcrypt('password'), 'role' => 'kaprog', 'is_active' => 1, 'jurusan_id' => $this->pplg->id]);
        $this->kaprogPplg = Guru::create(['user_id' => $kaprogUser->id, 'nip' => '198001012005011001', 'nama' => 'Kaprog PPLG', 'jenis_kelamin' => 'L', 'guru_type' => 'mapel']);
        \App\Models\KaprogAssignment::create(['guru_id' => $this->kaprogPplg->id, 'jurusan_id' => $this->pplg->id, 'is_active' => true, 'starts_on' => '2024-07-15']);

        $this->siswaX = $this->makeSiswa('24001', 'Siswa PPLG XII', 'L', $this->rombel12Pplg->id);
        StudentEnrollment::create(['siswa_id' => $this->siswaX->id, 'tahun_ajaran_id' => $ta->id, 'kelas_id' => $this->rombel12Pplg->id, 'jurusan_id' => $this->pplg->id, 'tingkat' => 12, 'tahun_ajaran' => '2026/2027', 'is_active' => true]);

        $siswaAnim = $this->makeSiswa('24002', 'Siswa Animasi XI', 'P', $this->rombel11Pplg->id);
        StudentEnrollment::create(['siswa_id' => $siswaAnim->id, 'tahun_ajaran_id' => $ta->id, 'kelas_id' => $this->rombel11Pplg->id, 'jurusan_id' => $this->pplg->id, 'tingkat' => 11, 'tahun_ajaran' => '2026/2027', 'is_active' => true]);
        $this->siswaKelas11 = $siswaAnim;
    }

    protected $siswaKelas11;

    private function makeSiswa(string $nis, string $nama, string $jk, int $kelasId): Siswa
    {
        $u = User::create(['name' => $nama, 'email' => 'siswa-' . $nis . '@test.local', 'password' => bcrypt('password'), 'role' => 'siswa', 'is_active' => 1]);
        return Siswa::create(['user_id' => $u->id, 'nis' => $nis, 'nama' => $nama, 'jenis_kelamin' => $jk, 'status' => 'aktif', 'kelas_id' => $kelasId]);
    }

    private function partner(string $nama, ?int $jurusanId, int $kuota = 0): PklDudiPartner
    {
        return PklDudiPartner::create([
            'nama_dudi' => $nama, 'bidang_industri' => 'IT', 'alamat' => 'Jl. Uji No 1',
            'kota' => 'Bandung', 'kontak_person' => 'PIC', 'jurusan_id' => $jurusanId,
            'kuota_siswa' => $kuota ?: null, 'status' => 'aktif',
        ]);
    }

    // ================= MITRA =================

    public function test_admin_can_update_partner(): void
    {
        $p = $this->partner('PT Lama', $this->pplg->id);
        $resp = $this->actingAs($this->admin)->put('/pkl/mitra-industri/' . $p->id, [
            'nama_dudi' => 'PT Baru', 'bidang_industri' => 'Software', 'alamat' => 'Jl. Baru 2',
            'kota' => 'Bandung', 'kontak_person' => 'PIC2', 'telepon' => '0812', 'status' => 'aktif', 'kuota_siswa' => 5,
        ]);
        $resp->assertRedirect(route('pkl.partners.index'))->assertSessionHas('success');
        $p->refresh();
        $this->assertSame('PT Baru', $p->nama_dudi);
        $this->assertSame(5, $p->kuota_siswa);
    }

    public function test_partner_delete_blocked_when_active_placement_exists(): void
    {
        $p = $this->partner('PT Aktif Sekali', $this->pplg->id);
        PklPlacement::create([
            'siswa_id' => $this->siswaX->id, 'pkl_dudi_partner_id' => $p->id,
            'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2027-01-31', 'status' => 'active',
        ]);
        $resp = $this->actingAs($this->admin)->delete('/pkl/mitra-industri/' . $p->id);
        $resp->assertSessionHas('error');
        $this->assertDatabaseHas('pkl_dudi_partners', ['id' => $p->id]);
    }

    public function test_partner_with_history_cannot_be_hard_deleted(): void
    {
        $p = $this->partner('PT Riwayat', $this->pplg->id);
        PklPlacement::create([
            'siswa_id' => $this->siswaX->id, 'pkl_dudi_partner_id' => $p->id,
            'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2027-01-31', 'status' => 'completed',
        ]);
        $this->actingAs($this->admin)->delete('/pkl/mitra-industri/' . $p->id)
            ->assertSessionHas('error');
        $this->assertDatabaseHas('pkl_dudi_partners', ['id' => $p->id]);
    }

    public function test_partner_without_history_can_be_deleted(): void
    {
        $p = $this->partner('PT Kosong', null);
        $this->actingAs($this->admin)->delete('/pkl/mitra-industri/' . $p->id)
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('pkl_dudi_partners', ['id' => $p->id]);
    }

    // ================= DEPENDENT DROPDOWNS =================

    public function test_rombel_options_only_kelas12_of_jurusan(): void
    {
        $resp = $this->actingAs($this->admin)->getJson('/pkl/options/rombel?jurusan_id=' . $this->pplg->id);
        $resp->assertOk();
        $ids = array_column($resp->json('data'), 'id');
        $this->assertContains($this->rombel12Pplg->id, $ids);
        $this->assertNotContains($this->rombel11Pplg->id, $ids, 'Kelas 11 must not appear');
        $this->assertNotContains($this->rombel12Anim->id, $ids, 'Rombel jurusan lain must not appear');
    }

    public function test_siswa_options_exclude_already_placed_students(): void
    {
        $p = $this->partner('PT Penuh Satu', $this->pplg->id);
        PklPlacement::create([
            'siswa_id' => $this->siswaX->id, 'pkl_dudi_partner_id' => $p->id,
            'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2027-01-31', 'status' => 'active',
        ]);
        $resp = $this->actingAs($this->admin)->getJson('/pkl/options/siswa?kelas_id=' . $this->rombel12Pplg->id);
        $resp->assertOk();
        $ids = array_column($resp->json('data'), 'id');
        $this->assertNotContains($this->siswaX->id, $ids, 'Siswa with active placement must be hidden');
    }

    public function test_mitra_options_exclude_full_quota_partners(): void
    {
        $full = $this->partner('PT Kuota Penuh', $this->pplg->id, 1);
        PklPlacement::create([
            'siswa_id' => $this->siswaX->id, 'pkl_dudi_partner_id' => $full->id,
            'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2027-01-31', 'status' => 'active',
        ]);
        $open = $this->partner('PT Kuota Luang', $this->pplg->id, 3);

        $resp = $this->actingAs($this->admin)->getJson('/pkl/options/mitra?jurusan_id=' . $this->pplg->id);
        $resp->assertOk();
        $ids = array_column($resp->json('data'), 'id');
        $this->assertNotContains($full->id, $ids, 'Full partner must not be selectable');
        $this->assertContains($open->id, $ids);
    }

    // ================= BACKEND RELATION VALIDATION =================

    public function test_store_rejects_kelas11_student(): void
    {
        $p = $this->partner('PT Valid', $this->pplg->id);
        $resp = $this->actingAs($this->admin)->post('/pkl/penempatan', [
            'siswa_id' => $this->siswaKelas11->id, 'pkl_dudi_partner_id' => $p->id,
            'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2027-01-31',
        ]);
        $resp->assertSessionHas('error', function ($msg) {
            return str_contains($msg, 'kelas 12');
        });
        $this->assertDatabaseCount('pkl_placements', 0);
    }

    public function test_store_rejects_wrong_rombel_pairing(): void
    {
        $p = $this->partner('PT Valid2', $this->pplg->id);
        $resp = $this->actingAs($this->admin)->post('/pkl/penempatan', [
            'siswa_id' => $this->siswaX->id, 'kelas_id' => $this->rombel12Anim->id,
            'pkl_dudi_partner_id' => $p->id,
            'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2027-01-31',
        ]);
        $resp->assertSessionHas('error');
        $this->assertDatabaseCount('pkl_placements', 0);
    }

    public function test_store_rejects_partner_from_other_jurusan(): void
    {
        $p = $this->partner('PT Animasi Saja', $this->anim->id);
        $resp = $this->actingAs($this->admin)->post('/pkl/penempatan', [
            'siswa_id' => $this->siswaX->id, 'pkl_dudi_partner_id' => $p->id,
            'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2027-01-31',
        ]);
        $resp->assertSessionHas('error', fn ($m) => str_contains($m, 'jurusan'));
        $this->assertDatabaseCount('pkl_placements', 0);
    }

    public function test_store_rejects_when_quota_full(): void
    {
        $p = $this->partner('PT Kuota 1', $this->pplg->id, 1);
        $lain = $this->makeSiswa('24009', 'Siswa Pengisi', 'L', $this->rombel12Pplg->id);
        PklPlacement::create([
            'siswa_id' => $lain->id, 'pkl_dudi_partner_id' => $p->id,
            'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2027-01-31', 'status' => 'active',
        ]);
        $resp = $this->actingAs($this->admin)->post('/pkl/penempatan', [
            'siswa_id' => $this->siswaX->id, 'pkl_dudi_partner_id' => $p->id,
            'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2027-01-31',
        ]);
        $resp->assertSessionHas('error', fn ($m) => str_contains($m, 'Kapasitas mitra penuh'));
    }

    public function test_store_accepts_valid_chain(): void
    {
        $p = $this->partner('PT Final', $this->pplg->id, 4);
        $resp = $this->actingAs($this->admin)->post('/pkl/penempatan', [
            'jurusan_id' => $this->pplg->id, 'kelas_id' => $this->rombel12Pplg->id,
            'siswa_id' => $this->siswaX->id, 'pkl_dudi_partner_id' => $p->id,
            'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2027-01-31',
        ]);
        $resp->assertRedirect(route('pkl.placements.index'))->assertSessionHas('success');
        $this->assertDatabaseHas('pkl_placements', [
            'siswa_id' => $this->siswaX->id, 'pkl_dudi_partner_id' => $p->id, 'status' => 'active',
        ]);
    }

    // ================= KAPROG SCOPING =================

    public function test_kaprog_cannot_place_student_from_other_jurusan(): void
    {
        $animSiswa = $this->makeSiswa('24005', 'Siswa Anim XII', 'P', $this->rombel12Anim->id);
        $p = $this->partner('PT Campur', null);
        $resp = $this->actingAs($this->kaprogPplg->user)->post('/pkl/penempatan', [
            'siswa_id' => $animSiswa->id, 'pkl_dudi_partner_id' => $p->id,
            'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2027-01-31',
        ]);
        $resp->assertSessionHas('error', fn ($m) => str_contains($m, 'Akses ditolak'));
        $this->assertDatabaseCount('pkl_placements', 0);
    }

    public function test_kaprog_rombel_options_clamped_to_own_jurusan(): void
    {
        $resp = $this->actingAs($this->kaprogPplg->user)->getJson('/pkl/options/rombel?jurusan_id=' . $this->anim->id);
        $resp->assertOk();
        $ids = array_column($resp->json('data'), 'id');
        $this->assertSame([$this->rombel12Pplg->id], $ids, 'Manipulated jurusan must be clamped to own department');
    }

    public function test_kaprog_siswa_options_reject_other_jurusan_kelas(): void
    {
        $resp = $this->actingAs($this->kaprogPplg->user)->getJson('/pkl/options/siswa?kelas_id=' . $this->rombel12Anim->id);
        $resp->assertForbidden();
    }

    public function test_kaprog_cannot_edit_placement_of_other_jurusan(): void
    {
        $animSiswa = $this->makeSiswa('24006', 'Siswa Anim XII2', 'P', $this->rombel12Anim->id);
        $p = $this->partner('PT Anim Mitra', $this->anim->id);
        $pl = PklPlacement::create([
            'siswa_id' => $animSiswa->id, 'pkl_dudi_partner_id' => $p->id,
            'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2027-01-31', 'status' => 'active',
        ]);
        $this->actingAs($this->kaprogPplg->user)
            ->put('/pkl/penempatan/' . $pl->id, [
                'pkl_dudi_partner_id' => $p->id, 'tanggal_mulai' => '2026-08-01',
                'tanggal_selesai' => '2027-01-31', 'status' => 'active',
            ])->assertForbidden();
    }

    public function test_kaprog_cannot_delete_partner_of_other_jurusan(): void
    {
        $p = $this->partner('PT Mitra Anim', $this->anim->id);
        $this->actingAs($this->kaprogPplg->user)->delete('/pkl/mitra-industri/' . $p->id)
            ->assertForbidden();
    }

    public function test_kaprog_store_partner_forced_to_own_jurusan(): void
    {
        $resp = $this->actingAs($this->kaprogPplg->user)->post('/pkl/mitra-industri', [
            'nama_dudi' => 'PT Titipan', 'bidang_industri' => 'IT', 'alamat' => 'Jl. X',
            'kota' => 'Bandung', 'kontak_person' => 'PIC', 'jurusan_id' => $this->anim->id,
        ]);
        $resp->assertSessionHas('error');
        $this->assertDatabaseMissing('pkl_dudi_partners', ['nama_dudi' => 'PT Titipan']);
    }

    // ================= PLACEMENT DESTROY =================

    public function test_destroy_with_history_becomes_withdrawn(): void
    {
        $p = $this->partner('PT Sejarah', $this->pplg->id);
        $pl = PklPlacement::create([
            'siswa_id' => $this->siswaX->id, 'pkl_dudi_partner_id' => $p->id,
            'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2027-01-31', 'status' => 'active',
        ]);
        \App\Models\PklLogbook::create([
            'pkl_placement_id' => $pl->id, 'tanggal' => '2026-08-02', 'aktivitas' => 'Kegiatan development modul absensi harian',
        ]);
        $this->actingAs($this->admin)->delete('/pkl/penempatan/' . $pl->id)->assertSessionHas('success');
        $pl->refresh();
        $this->assertSame('withdrawn', $pl->status);
        $this->assertDatabaseCount('pkl_logbooks', 1);
    }

    public function test_destroy_without_history_hard_deletes(): void
    {
        $p = $this->partner('PT Bersih', $this->pplg->id);
        $pl = PklPlacement::create([
            'siswa_id' => $this->siswaX->id, 'pkl_dudi_partner_id' => $p->id,
            'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2027-01-31', 'status' => 'active',
        ]);
        $this->actingAs($this->admin)->delete('/pkl/penempatan/' . $pl->id);
        $this->assertDatabaseMissing('pkl_placements', ['id' => $pl->id]);
    }
}
