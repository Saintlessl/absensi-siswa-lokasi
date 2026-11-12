<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\InventoryBorrowing;
use App\Models\InventoryBorrowingUnit;
use App\Models\InventoryItem;
use App\Models\InventoryUnit;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryBorrowingAccessAndMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $kaprogPplgUser;
    protected User $kaprogAnimUser;
    protected User $guruUser;
    protected User $siswaPplgUser;
    protected Siswa $siswaPplg;
    protected User $siswaAnimUser;
    protected Siswa $siswaAnim;

    protected Jurusan $jurusanPplg;
    protected Jurusan $jurusanAnim;

    protected InventoryItem $itemOmen16;
    protected InventoryItem $itemCameraAnim;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jurusanPplg = Jurusan::create([
            'kode_jurusan' => 'PPLG',
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'is_active'    => true,
        ]);

        $this->jurusanAnim = Jurusan::create([
            'kode_jurusan' => 'ANIM',
            'nama_jurusan' => 'Animasi',
            'is_active'    => true,
        ]);

        $kelasPplg = Kelas::create([
            'nama_kelas'   => 'XII PPLG 1',
            'jurusan_id'   => $this->jurusanPplg->id,
            'tingkat'      => 12,
            'tahun_ajaran' => '2026/2027',
            'is_active'    => true,
        ]);

        $kelasAnim = Kelas::create([
            'nama_kelas'   => 'XI ANIM 1',
            'jurusan_id'   => $this->jurusanAnim->id,
            'tingkat'      => 11,
            'tahun_ajaran' => '2026/2027',
            'is_active'    => true,
        ]);

        $this->superAdmin = User::create([
            'name'     => 'Super Administrator',
            'email'    => 'superadmin@skanic.test',
            'password' => bcrypt('password'),
            'role'     => 'superadmin',
        ]);

        $this->kaprogPplgUser = User::create([
            'name'       => 'Kaprog PPLG',
            'email'      => 'kaprog-pplg@skanic.test',
            'password'   => bcrypt('password'),
            'role'       => 'kaprog',
            'jurusan_id' => $this->jurusanPplg->id,
        ]);
        Guru::create([
            'user_id'       => $this->kaprogPplgUser->id,
            'nip'           => '198501012010011001',
            'nama'          => 'Budi Kaprog PPLG',
            'jenis_kelamin' => 'L',
            'guru_type'     => 'kaprog',
        ]);

        $this->kaprogAnimUser = User::create([
            'name'       => 'Kaprog Animasi',
            'email'      => 'kaprog-anim@skanic.test',
            'password'   => bcrypt('password'),
            'role'       => 'kaprog',
            'jurusan_id' => $this->jurusanAnim->id,
        ]);
        Guru::create([
            'user_id'       => $this->kaprogAnimUser->id,
            'nip'           => '198602022011012002',
            'nama'          => 'Siti Kaprog Anim',
            'jenis_kelamin' => 'P',
            'guru_type'     => 'kaprog',
        ]);

        $this->guruUser = User::create([
            'name'     => 'Guru Biasa',
            'email'    => 'guru@skanic.test',
            'password' => bcrypt('password'),
            'role'     => 'guru',
        ]);
        Guru::create([
            'user_id'       => $this->guruUser->id,
            'nip'           => '199003032015011003',
            'nama'          => 'Joko Guru',
            'jenis_kelamin' => 'L',
            'guru_type'     => 'biasa',
        ]);

        $this->siswaPplgUser = User::create([
            'name'     => 'Ahmad Siswa PPLG',
            'email'    => 'ahmad@skanic.test',
            'password' => bcrypt('password'),
            'role'     => 'siswa',
        ]);
        $this->siswaPplg = Siswa::create([
            'user_id'              => $this->siswaPplgUser->id,
            'nis'                  => '12345',
            'nisn'                 => '0012345678',
            'nama'                 => 'Ahmad Siswa PPLG',
            'kelas_id'             => $kelasPplg->id,
            'jurusan_id'           => $this->jurusanPplg->id,
            'jenis_kelamin'        => 'L',
            'status'               => 'aktif',
            'is_biometric_exempt'  => true,
        ]);

        $this->siswaAnimUser = User::create([
            'name'     => 'Rani Siswa Animasi',
            'email'    => 'rani@skanic.test',
            'password' => bcrypt('password'),
            'role'     => 'siswa',
        ]);
        $this->siswaAnim = Siswa::create([
            'user_id'              => $this->siswaAnimUser->id,
            'nis'                  => '54321',
            'nisn'                 => '0087654321',
            'nama'                 => 'Rani Siswa Animasi',
            'kelas_id'             => $kelasAnim->id,
            'jurusan_id'           => $this->jurusanAnim->id,
            'jenis_kelamin'        => 'P',
            'status'               => 'aktif',
            'is_biometric_exempt'  => true,
        ]);

        // Create PPLG item (OMEN 16) with 3 units
        $this->itemOmen16 = InventoryItem::create([
            'kode_barang'        => 'PPLG-LTP-OMEN16',
            'nama_barang'        => 'OMEN 16 Gaming Laptop',
            'jurusan_id'         => $this->jurusanPplg->id,
            'borrowing_scope'    => 'jurusan',
            'kategori'           => 'Laptop',
            'lokasi_penyimpanan' => 'Lab PPLG 1',
            'total_unit'         => 3,
            'unit_tersedia'      => 3,
            'kondisi'            => 'baik',
            'status'             => 'tersedia',
        ]);
        $this->itemOmen16->createUnits(3, 'baik', 'Lab PPLG 1', $this->kaprogPplgUser->id);

        // Create Animasi item (Kamera Sony) with 2 units
        $this->itemCameraAnim = InventoryItem::create([
            'kode_barang'        => 'ANIM-CAM-SONY',
            'nama_barang'        => 'Sony A6400 Camera',
            'jurusan_id'         => $this->jurusanAnim->id,
            'borrowing_scope'    => 'jurusan',
            'kategori'           => 'Kamera',
            'lokasi_penyimpanan' => 'Studio Animasi',
            'total_unit'         => 2,
            'unit_tersedia'      => 2,
            'kondisi'            => 'baik',
            'status'             => 'tersedia',
        ]);
        $this->itemCameraAnim->createUnits(2, 'baik', 'Studio Animasi', $this->kaprogAnimUser->id);
    }

    /**
     * Skenario A: Pengaturan Akses Peminjaman (Khusus Jurusan vs Umum)
     */
    public function test_skenario_a_borrowing_scope_access_control(): void
    {
        // 1. OMEN 16 starts as 'jurusan' (Khusus PPLG)
        $this->assertEquals('jurusan', $this->itemOmen16->borrowing_scope);

        // 2. Siswa PPLG can borrow
        $this->actingAs($this->siswaPplgUser);
        $res = $this->post(route('inventory.borrow.request'), [
            'inventory_item_id' => $this->itemOmen16->id,
            'jumlah'            => 1,
            'rencana_kembali'   => Carbon::tomorrow()->toDateString(),
            'keperluan'         => 'Coding project testing',
        ]);
        $res->assertSessionHas('success');
        $this->assertDatabaseHas('inventory_borrowings', [
            'inventory_item_id' => $this->itemOmen16->id,
            'user_id'           => $this->siswaPplgUser->id,
            'status'            => 'pending',
        ]);

        // 3. Siswa Animasi is rejected
        $this->actingAs($this->siswaAnimUser);
        $res = $this->post(route('inventory.borrow.request'), [
            'inventory_item_id' => $this->itemOmen16->id,
            'jumlah'            => 1,
            'rencana_kembali'   => Carbon::tomorrow()->toDateString(),
            'keperluan'         => 'Editing test',
        ]);
        $res->assertSessionHas('error');
        $this->assertStringContainsString('khusus untuk siswa', session('error'));

        // 4. Guru is rejected for 'jurusan'-scoped item
        $this->actingAs($this->guruUser);
        $res = $this->post(route('inventory.borrow.request'), [
            'inventory_item_id' => $this->itemOmen16->id,
            'jumlah'            => 1,
            'rencana_kembali'   => Carbon::tomorrow()->toDateString(),
            'keperluan'         => 'Teaching test',
        ]);
        $res->assertSessionHas('error');

        // 5. Kaprog PPLG updates OMEN 16 scope to 'umum'
        $this->actingAs($this->kaprogPplgUser);
        $res = $this->put(route('inventory.update', $this->itemOmen16->id), [
            'nama_barang'        => 'OMEN 16 Gaming Laptop',
            'kategori'           => 'Laptop',
            'lokasi_penyimpanan' => 'Lab PPLG 1',
            'borrowing_scope'    => 'umum',
        ]);
        $res->assertSessionHas('success');
        $this->assertEquals('umum', $this->itemOmen16->fresh()->borrowing_scope);

        // 6. Now Siswa Animasi CAN borrow
        $this->actingAs($this->siswaAnimUser);
        $res = $this->post(route('inventory.borrow.request'), [
            'inventory_item_id' => $this->itemOmen16->id,
            'jumlah'            => 1,
            'rencana_kembali'   => Carbon::tomorrow()->toDateString(),
            'keperluan'         => '3D Rendering',
        ]);
        $res->assertSessionHas('success');

        // 7. Guru can also borrow now
        $this->actingAs($this->guruUser);
        $res = $this->post(route('inventory.borrow.request'), [
            'inventory_item_id' => $this->itemOmen16->id,
            'jumlah'            => 1,
            'rencana_kembali'   => Carbon::tomorrow()->toDateString(),
            'keperluan'         => 'Class demonstration',
        ]);
        $res->assertSessionHas('success');
    }

    /**
     * Skenario B: Monitoring Kaprog (Department Scoping & 3 Tabs)
     */
    public function test_skenario_b_kaprog_monitoring_scoping_and_tabs(): void
    {
        $today = Carbon::today('Asia/Jakarta');

        // 1. Create PPLG active borrowed (Sedang Dipinjam)
        $b1 = InventoryBorrowing::create([
            'inventory_item_id' => $this->itemOmen16->id,
            'user_id'           => $this->siswaPplgUser->id,
            'siswa_id'          => $this->siswaPplg->id,
            'jumlah'            => 1,
            'tanggal_pinjam'    => $today->toDateString(),
            'rencana_kembali'   => $today->copy()->addDays(3)->toDateString(),
            'status'            => 'borrowed',
            'keperluan'         => 'PPLG Active Task',
        ]);

        // 2. Create PPLG returned (Dikembalikan)
        $b2 = InventoryBorrowing::create([
            'inventory_item_id' => $this->itemOmen16->id,
            'user_id'           => $this->siswaPplgUser->id,
            'siswa_id'          => $this->siswaPplg->id,
            'jumlah'            => 1,
            'tanggal_pinjam'    => $today->copy()->subDays(5)->toDateString(),
            'rencana_kembali'   => $today->copy()->subDays(2)->toDateString(),
            'tanggal_kembali'   => $today->copy()->subDays(1)->toDateString(),
            'status'            => 'returned',
            'keperluan'         => 'PPLG Returned Task',
        ]);

        // 3. Create PPLG overdue (Belum Dikembalikan)
        $b3 = InventoryBorrowing::create([
            'inventory_item_id' => $this->itemOmen16->id,
            'user_id'           => $this->siswaPplgUser->id,
            'siswa_id'          => $this->siswaPplg->id,
            'jumlah'            => 1,
            'tanggal_pinjam'    => $today->copy()->subDays(4)->toDateString(),
            'rencana_kembali'   => $today->copy()->subDays(1)->toDateString(), // past due!
            'tanggal_kembali'   => null,
            'status'            => 'borrowed',
            'keperluan'         => 'PPLG Overdue Task',
        ]);

        // 4. Create Animasi borrowed transaction
        $bAnim = InventoryBorrowing::create([
            'inventory_item_id' => $this->itemCameraAnim->id,
            'user_id'           => $this->siswaAnimUser->id,
            'siswa_id'          => $this->siswaAnim->id,
            'jumlah'            => 1,
            'tanggal_pinjam'    => $today->toDateString(),
            'rencana_kembali'   => $today->copy()->addDays(2)->toDateString(),
            'status'            => 'borrowed',
            'keperluan'         => 'Animasi Secret Camera',
        ]);

        // Kaprog PPLG accesses /inventaris
        $this->actingAs($this->kaprogPplgUser);

        // Tab: Sedang Dipinjam
        $res = $this->get(route('inventory.index', ['monitoring_tab' => 'dipinjam']));
        $res->assertOk();
        $res->assertSee('PPLG Active Task');
        $res->assertDontSee('Animasi Secret Camera'); // Scoped out!
        $res->assertDontSee('PPLG Returned Task');
        $res->assertDontSee('PPLG Overdue Task');

        // Tab: Dikembalikan
        $res = $this->get(route('inventory.index', ['monitoring_tab' => 'dikembalikan']));
        $res->assertOk();
        $res->assertSee('PPLG Returned Task');
        $res->assertDontSee('PPLG Active Task');
        $res->assertDontSee('Animasi Secret Camera');

        // Tab: Belum Dikembalikan
        $res = $this->get(route('inventory.index', ['monitoring_tab' => 'belum_dikembalikan']));
        $res->assertOk();
        $res->assertSee('PPLG Overdue Task');
        $res->assertDontSee('PPLG Active Task');
        $res->assertDontSee('PPLG Returned Task');
        $res->assertDontSee('Animasi Secret Camera');
    }

    /**
     * Skenario C: Monitoring Super Admin (All Departments & Filter Jurusan)
     */
    public function test_skenario_c_super_admin_monitoring_and_department_filter(): void
    {
        $today = Carbon::today('Asia/Jakarta');

        InventoryBorrowing::create([
            'inventory_item_id' => $this->itemOmen16->id,
            'user_id'           => $this->siswaPplgUser->id,
            'siswa_id'          => $this->siswaPplg->id,
            'jumlah'            => 1,
            'tanggal_pinjam'    => $today->toDateString(),
            'rencana_kembali'   => $today->copy()->addDays(3)->toDateString(),
            'status'            => 'borrowed',
            'keperluan'         => 'PPLG Laptop Monitoring',
        ]);

        InventoryBorrowing::create([
            'inventory_item_id' => $this->itemCameraAnim->id,
            'user_id'           => $this->siswaAnimUser->id,
            'siswa_id'          => $this->siswaAnim->id,
            'jumlah'            => 1,
            'tanggal_pinjam'    => $today->toDateString(),
            'rencana_kembali'   => $today->copy()->addDays(2)->toDateString(),
            'status'            => 'borrowed',
            'keperluan'         => 'Animasi Camera Monitoring',
        ]);

        $this->actingAs($this->superAdmin);

        // 1. Without filter: sees both
        $res = $this->get(route('inventory.index', ['monitoring_tab' => 'dipinjam']));
        $res->assertOk();
        $res->assertSee('PPLG Laptop Monitoring');
        $res->assertSee('Animasi Camera Monitoring');

        // 2. Filter by PPLG
        $res = $this->get(route('inventory.index', [
            'monitoring_tab'        => 'dipinjam',
            'monitoring_jurusan_id' => $this->jurusanPplg->id,
        ]));
        $res->assertOk();
        $res->assertSee('PPLG Laptop Monitoring');
        $res->assertDontSee('Animasi Camera Monitoring');

        // 3. Filter by Animasi
        $res = $this->get(route('inventory.index', [
            'monitoring_tab'        => 'dipinjam',
            'monitoring_jurusan_id' => $this->jurusanAnim->id,
        ]));
        $res->assertOk();
        $res->assertSee('Animasi Camera Monitoring');
        $res->assertDontSee('PPLG Laptop Monitoring');
    }

    /**
     * Skenario D: Detail Kelompok Barang Monitoring Tabs
     */
    public function test_skenario_d_item_detail_monitoring_tabs(): void
    {
        $today = Carbon::today('Asia/Jakarta');

        // OMEN 16 borrowing
        InventoryBorrowing::create([
            'inventory_item_id' => $this->itemOmen16->id,
            'user_id'           => $this->siswaPplgUser->id,
            'siswa_id'          => $this->siswaPplg->id,
            'jumlah'            => 1,
            'tanggal_pinjam'    => $today->toDateString(),
            'rencana_kembali'   => $today->copy()->addDays(2)->toDateString(),
            'status'            => 'borrowed',
            'keperluan'         => 'Khusus OMEN 16 Borrowing',
        ]);

        // Camera borrowing
        InventoryBorrowing::create([
            'inventory_item_id' => $this->itemCameraAnim->id,
            'user_id'           => $this->siswaAnimUser->id,
            'siswa_id'          => $this->siswaAnim->id,
            'jumlah'            => 1,
            'tanggal_pinjam'    => $today->toDateString(),
            'rencana_kembali'   => $today->copy()->addDays(2)->toDateString(),
            'status'            => 'borrowed',
            'keperluan'         => 'Khusus Kamera Sony Borrowing',
        ]);

        $this->actingAs($this->superAdmin);

        // Open OMEN 16 detail
        $res = $this->get(route('inventory.show', $this->itemOmen16->id));
        $res->assertOk();
        $res->assertSee('Khusus OMEN 16 Borrowing');
        $res->assertDontSee('Khusus Kamera Sony Borrowing'); // Does NOT leak other items

        // Open Camera detail
        $res = $this->get(route('inventory.show', $this->itemCameraAnim->id));
        $res->assertOk();
        $res->assertSee('Khusus Kamera Sony Borrowing');
        $res->assertDontSee('Khusus OMEN 16 Borrowing');
    }

    /**
     * Skenario E: Status Otomatis & Dynamic Overdue Lifecycle
     */
    public function test_skenario_e_automatic_status_lifecycle_and_due_dates(): void
    {
        $today = Carbon::today('Asia/Jakarta');

        $b = InventoryBorrowing::create([
            'inventory_item_id' => $this->itemOmen16->id,
            'user_id'           => $this->siswaPplgUser->id,
            'siswa_id'          => $this->siswaPplg->id,
            'jumlah'            => 1,
            'tanggal_pinjam'    => $today->toDateString(),
            'rencana_kembali'   => $today->copy()->addDays(2)->toDateString(),
            'status'            => 'borrowed',
            'keperluan'         => 'Lifecycle testing',
        ]);

        // 1. Due date is in future => Sedang Dipinjam
        $this->assertEquals('borrowed', $b->effective_status);
        $this->assertFalse($b->is_overdue);

        // 2. Change rencana_kembali to yesterday => Belum Dikembalikan (overdue)
        $b->update(['rencana_kembali' => $today->copy()->subDay()->toDateString()]);
        $b->refresh();

        $this->assertEquals('overdue', $b->effective_status);
        $this->assertTrue($b->is_overdue);

        // 3. Confirm return => Dikembalikan (even if returned late, status is returned!)
        $b->update([
            'status'          => 'returned',
            'tanggal_kembali' => $today->toDateString(),
        ]);
        $b->refresh();

        $this->assertEquals('returned', $b->effective_status);
        $this->assertFalse($b->is_overdue);
    }

    /**
     * Skenario F: Keamanan & Authorization Scoping
     */
    public function test_skenario_f_security_and_authorization(): void
    {
        // 1. Kaprog PPLG tries to access detail of Animasi item => 403
        $this->actingAs($this->kaprogPplgUser);
        $res = $this->get(route('inventory.show', $this->itemCameraAnim->id));
        $res->assertStatus(403);

        // 2. Kaprog PPLG tries to edit Animasi item => 403
        $res = $this->put(route('inventory.update', $this->itemCameraAnim->id), [
            'nama_barang'        => 'Hacked Item',
            'kategori'           => 'Hacked',
            'lokasi_penyimpanan' => 'Hacked',
        ]);
        $res->assertStatus(403);

        // 3. Siswa tries to edit borrowing_scope => 403
        $this->actingAs($this->siswaPplgUser);
        $res = $this->putJson(route('inventory.update', $this->itemOmen16->id), [
            'nama_barang'     => 'Changed by student',
            'kategori'        => 'Laptop',
            'borrowing_scope' => 'umum',
        ]);
        $res->assertStatus(403);
    }
}
