<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\InventoryBorrowing;
use App\Models\InventoryItem;
use App\Models\Jurusan;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Manual inventory transactions (peminjaman/pengembalian walk-in oleh petugas).
 */
class InventoryManualTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $kaprogAnim;
    protected $itemPplg;
    protected $itemAnim;
    protected $siswa;
    protected $guruPinjam;

    protected function setUp(): void
    {
        parent::setUp();

        $pplg = Jurusan::create(['kode_jurusan' => 'PPLG', 'nama_jurusan' => 'PPLG', 'is_active' => true]);
        $anim = Jurusan::create(['kode_jurusan' => 'ANIM', 'nama_jurusan' => 'Animasi', 'is_active' => true]);

        $kelas = \App\Models\Kelas::create(['nama_kelas' => 'XII PPLG 1', 'jurusan_id' => $pplg->id, 'tingkat' => 12, 'nomor_rombel' => 1, 'tahun_ajaran' => '2026/2027', 'is_active' => true]);

        $this->admin = User::create(['name' => 'Admin Inv', 'email' => 'admin-inv@test.local', 'password' => bcrypt('password'), 'role' => 'admin', 'is_active' => 1]);

        $kaprogUser = User::create(['name' => 'Kaprog Anim', 'email' => 'kaprog-anim@test.local', 'password' => bcrypt('password'), 'role' => 'kaprog', 'is_active' => 1, 'jurusan_id' => $anim->id]);
        $this->kaprogAnim = Guru::create(['user_id' => $kaprogUser->id, 'nip' => '198202022008012002', 'nama' => 'Kaprog Anim', 'jenis_kelamin' => 'P', 'guru_type' => 'mapel']);

        $siswaUser = User::create(['name' => 'Siswa Pinjam', 'email' => 'siswa-inv@test.local', 'password' => bcrypt('password'), 'role' => 'siswa', 'is_active' => 1]);
        $this->siswa = Siswa::create(['user_id' => $siswaUser->id, 'nis' => '26100', 'nama' => 'Siswa Pinjam', 'jenis_kelamin' => 'L', 'status' => 'aktif', 'kelas_id' => $kelas->id]);

        $guruUser = User::create(['name' => 'Guru Pinjam', 'email' => 'guru-inv@test.local', 'password' => bcrypt('password'), 'role' => 'guru', 'is_active' => 1]);
        $this->guruPinjam = Guru::create(['user_id' => $guruUser->id, 'nip' => '198303032010011003', 'nama' => 'Guru Pinjam', 'jenis_kelamin' => 'L', 'guru_type' => 'mapel']);

        $this->itemPplg = InventoryItem::create([
            'kode_barang' => 'LTP-01', 'nama_barang' => 'Laptop Praktikum', 'jurusan_id' => $pplg->id,
            'kategori' => 'Komputer', 'lokasi_penyimpanan' => 'Lab 1', 'total_unit' => 5,
            'unit_tersedia' => 5, 'kondisi' => 'baik', 'status' => 'tersedia',
        ]);
        $this->itemAnim = InventoryItem::create([
            'kode_barang' => 'PEN-01', 'nama_barang' => 'Pen Tablet Animasi', 'jurusan_id' => $anim->id,
            'kategori' => 'Desain', 'lokasi_penyimpanan' => 'Lab Animasi', 'total_unit' => 3,
            'unit_tersedia' => 3, 'kondisi' => 'baik', 'status' => 'tersedia',
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'jenis_peminjam' => 'siswa',
            'siswa_id' => $this->siswa->id,
            'inventory_item_id' => $this->itemPplg->id,
            'jumlah' => 2,
            'tanggal_pinjam' => now()->toDateString(),
            'rencana_kembali' => now()->addWeek()->toDateString(),
            'keperluan' => 'Praktikum jaringan',
        ], $overrides);
    }

    public function test_manual_borrow_for_siswa_creates_borrowed_row_and_deducts_stock(): void
    {
        $resp = $this->actingAs($this->admin)->post('/peminjaman-alat/manual', $this->payload());
        $resp->assertSessionHas('success');

        $b = InventoryBorrowing::first();
        $this->assertNotNull($b);
        $this->assertSame('borrowed', $b->status);
        $this->assertSame('manual_petugas', $b->sumber_transaksi);
        $this->assertSame($this->admin->id, $b->dicatat_oleh);
        $this->assertSame($this->siswa->id, $b->siswa_id);
        $this->assertSame($this->siswa->user_id, $b->user_id);
        $this->assertNull($b->peminjam_guru_id);

        $this->itemPplg->refresh();
        $this->assertSame(3, $this->itemPplg->unit_tersedia);
        $this->assertSame('tersedia', $this->itemPplg->status);
    }

    public function test_manual_borrow_for_guru_links_guru_identity(): void
    {
        $resp = $this->actingAs($this->admin)->post('/peminjaman-alat/manual', $this->payload([
            'jenis_peminjam' => 'guru', 'siswa_id' => null, 'guru_id' => $this->guruPinjam->id,
        ]));
        $resp->assertSessionHas('success');
        $b = InventoryBorrowing::first();
        $this->assertSame($this->guruPinjam->id, $b->peminjam_guru_id);
        $this->assertSame($this->guruPinjam->user_id, $b->user_id);
        $this->assertNull($b->siswa_id);
    }

    public function test_manual_borrow_rejects_amount_above_stock(): void
    {
        $resp = $this->actingAs($this->admin)->post('/peminjaman-alat/manual', $this->payload(['jumlah' => 9]));
        $resp->assertSessionHas('error');
        $this->assertDatabaseCount('inventory_borrowings', 0);
        $this->itemPplg->refresh();
        $this->assertSame(5, $this->itemPplg->unit_tersedia, 'stok must not change on rejected transaction');
    }

    public function test_manual_borrow_status_borrowed_shows_in_history(): void
    {
        $this->actingAs($this->admin)->post('/peminjaman-alat/manual', $this->payload());
        $resp = $this->actingAs($this->admin)->get('/peminjaman-alat');
        $resp->assertOk()->assertSee('MANUAL PETUGAS');
    }

    public function test_manual_return_restores_stock_once(): void
    {
        $this->actingAs($this->admin)->post('/peminjaman-alat/manual', $this->payload());
        $b = InventoryBorrowing::first();

        $this->actingAs($this->admin)->post('/peminjaman-alat/' . $b->id . '/return-manual', [
            'kondisi_sesudah' => 'baik', 'tanggal_kembali' => now()->toDateString(),
        ])->assertSessionHas('success');

        $this->itemPplg->refresh();
        $this->assertSame(5, $this->itemPplg->unit_tersedia, 'full stock restored');
        $b->refresh();
        $this->assertSame('returned', $b->status);

        // double return must NOT credit again
        $this->actingAs($this->admin)->post('/peminjaman-alat/' . $b->id . '/return-manual', [
            'kondisi_sesudah' => 'baik', 'tanggal_kembali' => now()->toDateString(),
        ])->assertSessionHas('error');
        $this->itemPplg->refresh();
        $this->assertSame(5, $this->itemPplg->unit_tersedia);
    }

    public function test_manual_return_rusak_berat_quarantines_item(): void
    {
        $this->actingAs($this->admin)->post('/peminjaman-alat/manual', $this->payload(['jumlah' => 1]));
        $b = InventoryBorrowing::first();
        $this->actingAs($this->admin)->post('/peminjaman-alat/' . $b->id . '/return-manual', [
            'kondisi_sesudah' => 'rusak_berat', 'tanggal_kembali' => now()->toDateString(),
        ]);
        $this->itemPplg->refresh();
        $this->assertSame('maintenance', $this->itemPplg->status);
        $this->assertSame(4, $this->itemPplg->unit_tersedia, 'broken unit must not re-enter available stock');
    }

    public function test_kaprog_cannot_manual_borrow_other_jurusan_asset(): void
    {
        $resp = $this->actingAs($this->kaprogAnim->user)->post('/peminjaman-alat/manual', $this->payload());
        $resp->assertForbidden();
        $this->assertDatabaseCount('inventory_borrowings', 0);
    }

    public function test_kaprog_can_manual_borrow_own_jurusan_asset(): void
    {
        $resp = $this->actingAs($this->kaprogAnim->user)->post('/peminjaman-alat/manual', $this->payload([
            'inventory_item_id' => $this->itemAnim->id, 'jumlah' => 1,
        ]));
        $resp->assertSessionHas('success');
        $this->itemAnim->refresh();
        $this->assertSame(2, $this->itemAnim->unit_tersedia);
    }

    public function test_siswa_cannot_post_manual_transactions(): void
    {
        $resp = $this->actingAs($this->siswa->user)->post('/peminjaman-alat/manual', $this->payload());
        // Blocked before reaching the controller (biometric-gate or role middleware);
        // the decisive assertion is that no transaction row exists.
        $resp->assertRedirect();
        $this->assertDatabaseCount('inventory_borrowings', 0);
    }

    public function test_options_endpoint_returns_items(): void
    {
        $resp = $this->actingAs($this->admin)->getJson('/peminjaman-alat/options');
        $resp->assertOk()->assertJsonStructure(['siswa', 'guru', 'items']);
        $ids = array_column($resp->json('items'), 'id');
        $this->assertContains($this->itemPplg->id, $ids);
        $this->assertContains($this->itemAnim->id, $ids);
    }

    public function test_kaprog_options_scoped_to_own_jurusan(): void
    {
        $resp = $this->actingAs($this->kaprogAnim->user)->getJson('/peminjaman-alat/options');
        $resp->assertOk();
        $ids = array_column($resp->json('items'), 'id');
        $this->assertContains($this->itemAnim->id, $ids);
        $this->assertNotContains($this->itemPplg->id, $ids, 'kaprog must not see other jurusan assets');
    }
}
