<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\InventoryBorrowing;
use App\Models\InventoryItem;
use App\Models\InventoryUnit;
use App\Models\InventoryUnitLog;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAssetManagementReworkTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $kaprogPplgUser;
    protected Guru $kaprogPplg;
    protected User $kaprogAnimUser;
    protected Guru $kaprogAnim;
    protected User $guruUser;
    protected Guru $guru;
    protected User $siswaUser1;
    protected Siswa $siswa1;
    protected User $siswaUser2;
    protected Siswa $siswa2;

    protected Jurusan $jurusanPplg;
    protected Jurusan $jurusanAnim;

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
        $this->kaprogPplg = Guru::create([
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
        $this->kaprogAnim = Guru::create([
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
        $this->guru = Guru::create([
            'user_id'       => $this->guruUser->id,
            'nip'           => '199003032015011003',
            'nama'          => 'Ahmad Guru',
            'jenis_kelamin' => 'L',
            'guru_type'     => 'mapel',
        ]);

        $this->siswaUser1 = User::create([
            'name'     => 'Fajar Siswa',
            'email'    => 'fajar@skanic.test',
            'password' => bcrypt('password'),
            'role'     => 'siswa',
        ]);
        $this->siswa1 = Siswa::create([
            'user_id'              => $this->siswaUser1->id,
            'nis'                  => '26101',
            'nama'                 => 'Fajar Siswa',
            'kelas_id'             => $kelasPplg->id,
            'jurusan_id'           => $this->jurusanPplg->id,
            'status'               => 'aktif',
            'is_biometric_exempt'  => true,
            'biometric_status'     => 'enrolled',
        ]);

        $this->siswaUser2 = User::create([
            'name'     => 'Dewi Siswa',
            'email'    => 'dewi@skanic.test',
            'password' => bcrypt('password'),
            'role'     => 'siswa',
        ]);
        $this->siswa2 = Siswa::create([
            'user_id'              => $this->siswaUser2->id,
            'nis'                  => '26102',
            'nama'                 => 'Dewi Siswa',
            'kelas_id'             => $kelasPplg->id,
            'jurusan_id'           => $this->jurusanPplg->id,
            'status'               => 'aktif',
            'is_biometric_exempt'  => true,
            'biometric_status'     => 'enrolled',
        ]);
    }

    /**
     * Skenario A: Menambah Stok & Physical Unit Generation
     */
    public function test_skenario_a_create_asset_group_and_units(): void
    {
        // 1. Kaprog creates OMEN 16 group with 5 units
        $resp = $this->actingAs($this->kaprogPplgUser)->post(route('inventory.store'), [
            'kode_barang'        => 'PPLG-LTP-OMEN16',
            'nama_barang'        => 'OMEN 16',
            'jurusan_id'         => $this->jurusanPplg->id,
            'kategori'           => 'Laptop',
            'merk'               => 'HP',
            'tipe_model'         => 'OMEN 16 Gaming Laptop',
            'lokasi_penyimpanan' => 'Kantor PPLG',
            'deskripsi'          => 'Laptop gaming untuk lab coding & render game',
            'total_unit'         => 5,
            'kondisi'            => 'baik',
        ]);

        $resp->assertRedirect(route('inventory.index'));
        $item = InventoryItem::where('kode_barang', 'PPLG-LTP-OMEN16')->first();
        $this->assertNotNull($item);
        $this->assertEquals(5, $item->total_unit);
        $this->assertEquals(5, $item->unit_tersedia);

        // 2. Check 5 individual physical units created
        $units = InventoryUnit::where('inventory_item_id', $item->id)->get();
        $this->assertCount(5, $units);

        // 3. Verify unique unit codes and QR tokens
        $unitCodes = $units->pluck('kode_unit')->toArray();
        $this->assertCount(5, array_unique($unitCodes), 'Setiap unit harus memiliki kode unik');

        foreach ($units as $unit) {
            $this->assertStringStartsWith('PPLG-LTP-OMEN16-', $unit->kode_unit);
            $this->assertEquals('baik', $unit->kondisi);
            $this->assertEquals('tersedia', $unit->status_ketersediaan);
            $this->assertNotEmpty($unit->qr_token);
        }

        // 4. Verify audit log created for each unit
        $this->assertEquals(5, InventoryUnitLog::whereIn('inventory_unit_id', $units->pluck('id'))->count());
    }

    /**
     * Skenario B: Individual Unit Condition & Availability
     */
    public function test_skenario_b_individual_condition_change_and_availability(): void
    {
        $item = InventoryItem::create([
            'kode_barang'        => 'PPLG-LTP-OMEN16',
            'nama_barang'        => 'OMEN 16',
            'jurusan_id'         => $this->jurusanPplg->id,
            'kategori'           => 'Laptop',
            'lokasi_penyimpanan' => 'Kantor PPLG',
            'total_unit'         => 5,
            'unit_tersedia'      => 5,
            'kondisi'            => 'baik',
            'status'             => 'tersedia',
        ]);
        $units = $item->createUnits(5, 'baik');

        $unit1 = $units[0];
        $unit2 = $units[1];

        // Kaprog updates Unit 1 to 'rusak_ringan'
        $resp = $this->actingAs($this->kaprogPplgUser)->put(route('inventory.units.update', $unit1->id), [
            'kondisi'              => 'rusak_ringan',
            'lokasi_penyimpanan'   => 'Meja Servis Lab PPLG',
            'keterangan_perubahan' => 'Keyboard tombol W dan D macet',
        ]);
        $resp->assertSessionHas('success');

        $unit1->refresh();
        $unit2->refresh();
        $item->refresh();

        // Ensure Unit 1 changed, but Unit 2 remained 'baik'
        $this->assertEquals('rusak_ringan', $unit1->kondisi);
        $this->assertEquals('baik', $unit2->kondisi);
        $this->assertFalse($unit1->isEligibleForLoan(), 'Unit dengan kondisi rusak ringan tidak boleh dipinjamkan');
        $this->assertTrue($unit2->isEligibleForLoan());

        // Group stock count updated
        $this->assertEquals(4, $item->countAvailableUnits());
        $this->assertEquals(4, $item->unit_tersedia);

        // Condition summary updated
        $breakdown = $item->condition_breakdown;
        $this->assertEquals(4, $breakdown['baik']);
        $this->assertEquals(1, $breakdown['rusak_ringan']);

        // Audit log recorded
        $log = InventoryUnitLog::where('inventory_unit_id', $unit1->id)
            ->where('action', 'kondisi_diubah')
            ->first();
        $this->assertNotNull($log);
        $this->assertEquals('baik', $log->kondisi_lama);
        $this->assertEquals('rusak_ringan', $log->kondisi_baru);
    }

    /**
     * Skenario C: Borrowing, Approval, Reservation, and Handover Lifecycle
     */
    public function test_skenario_c_borrow_request_approve_reserve_and_handover(): void
    {
        $item = InventoryItem::create([
            'kode_barang'        => 'PPLG-LTP-OMEN16',
            'nama_barang'        => 'OMEN 16',
            'jurusan_id'         => $this->jurusanPplg->id,
            'kategori'           => 'Laptop',
            'lokasi_penyimpanan' => 'Kantor PPLG',
            'total_unit'         => 3,
            'unit_tersedia'      => 3,
            'kondisi'            => 'baik',
            'status'             => 'tersedia',
        ]);
        $item->createUnits(3, 'baik');

        // 1. Siswa requests 1 unit
        $resp = $this->actingAs($this->siswaUser1)->post(route('inventory.borrow.request'), [
            'inventory_item_id' => $item->id,
            'jumlah'            => 1,
            'rencana_kembali'   => Carbon::tomorrow()->toDateString(),
            'keperluan'         => 'Praktik pembuatan video',
        ]);
        $resp->assertSessionHas('success');

        $borrowing = InventoryBorrowing::where('inventory_item_id', $item->id)->firstOrFail();
        $this->assertEquals('pending', $borrowing->status);

        // 2. Kaprog approves the request
        $respApprove = $this->actingAs($this->kaprogPplgUser)->post(route('inventory.borrow.approve', $borrowing->id));
        $respApprove->assertSessionHas('success');

        $borrowing->refresh();
        $this->assertEquals('approved', $borrowing->status);

        // Verify that 1 physical unit was allocated and reserved
        $this->assertCount(1, $borrowing->borrowingUnits);
        $bu = $borrowing->borrowingUnits->first();
        $this->assertEquals('reserved', $bu->status);

        $allocatedUnit = $bu->unit;
        $this->assertNotNull($allocatedUnit);
        // Important: Physical availability remains 'tersedia' until handover, but reserved
        $this->assertEquals('tersedia', $allocatedUnit->status_ketersediaan);
        $this->assertTrue($allocatedUnit->isReserved(), 'Unit harus berstatus reserved');
        $this->assertFalse($allocatedUnit->isEligibleForLoan(), 'Unit yang direservasi tidak boleh dialokasikan ke permohonan lain');

        // Available count decremented
        $item->refresh();
        $this->assertEquals(2, $item->countAvailableUnits());

        // 3. Petugas confirms handover
        $respHandover = $this->actingAs($this->kaprogPplgUser)->post(route('inventory.borrow.handover', $borrowing->id));
        $respHandover->assertSessionHas('success');

        $borrowing->refresh();
        $allocatedUnit->refresh();

        // Status of transaction becomes 'borrowed', unit status becomes 'dipinjam'
        $this->assertEquals('borrowed', $borrowing->status);
        $this->assertEquals('dipinjam', $allocatedUnit->status_ketersediaan);
        $this->assertNotNull($borrowing->diserahkan_at);
        $this->assertEquals($this->kaprogPplgUser->id, $borrowing->diserahkan_oleh);
    }

    /**
     * Skenario D: Return Process and Condition Verification
     */
    public function test_skenario_d_return_and_physical_inspection(): void
    {
        $item = InventoryItem::create([
            'kode_barang'        => 'PPLG-LTP-OMEN16',
            'nama_barang'        => 'OMEN 16',
            'jurusan_id'         => $this->jurusanPplg->id,
            'kategori'           => 'Laptop',
            'lokasi_penyimpanan' => 'Kantor PPLG',
            'total_unit'         => 1,
            'unit_tersedia'      => 1,
            'kondisi'            => 'baik',
            'status'             => 'tersedia',
        ]);
        $units = $item->createUnits(1, 'baik');
        $unit = $units[0];

        $borrowing = InventoryBorrowing::create([
            'inventory_item_id' => $item->id,
            'user_id'           => $this->siswaUser1->id,
            'siswa_id'          => $this->siswa1->id,
            'jumlah'            => 1,
            'tanggal_pinjam'    => now()->toDateString(),
            'rencana_kembali'   => Carbon::tomorrow()->toDateString(),
            'keperluan'         => 'Praktikum',
            'status'            => 'borrowed',
            'diserahkan_oleh'   => $this->kaprogPplgUser->id,
            'diserahkan_at'     => now(),
        ]);

        $bu = \App\Models\InventoryBorrowingUnit::create([
            'inventory_borrowing_id' => $borrowing->id,
            'inventory_unit_id'      => $unit->id,
            'status'                 => 'borrowed',
            'diserahkan_at'          => now(),
        ]);
        $unit->update(['status_ketersediaan' => 'dipinjam']);
        $item->syncStockCounts();

        // Return with condition inspection 'baik'
        $resp = $this->actingAs($this->kaprogPplgUser)->post(route('inventory.borrow.return', $borrowing->id), [
            'kondisi_sesudah'      => 'baik',
            'catatan_pengembalian' => 'Laptop mulus dan charger lengkap.',
        ]);
        $resp->assertSessionHas('success');

        $borrowing->refresh();
        $unit->refresh();
        $item->refresh();

        $this->assertEquals('returned', $borrowing->status);
        $this->assertEquals('tersedia', $unit->status_ketersediaan);
        $this->assertEquals('baik', $unit->kondisi);
        $this->assertEquals(1, $item->countAvailableUnits());
    }

    /**
     * Skenario E: Overdue (Keterlambatan) Dynamic Status Computation
     */
    public function test_skenario_e_overdue_dynamic_status(): void
    {
        $item = InventoryItem::create([
            'kode_barang'        => 'PPLG-LTP-OMEN16',
            'nama_barang'        => 'OMEN 16',
            'jurusan_id'         => $this->jurusanPplg->id,
            'kategori'           => 'Laptop',
            'lokasi_penyimpanan' => 'Kantor PPLG',
            'total_unit'         => 1,
            'unit_tersedia'      => 0,
            'kondisi'            => 'baik',
            'status'             => 'dipinjam',
        ]);
        $units = $item->createUnits(1, 'baik');
        $unit = $units[0];
        $unit->update(['status_ketersediaan' => 'dipinjam']);

        // Transaction with deadline yesterday (passed)
        $borrowing = InventoryBorrowing::create([
            'inventory_item_id' => $item->id,
            'user_id'           => $this->siswaUser1->id,
            'siswa_id'          => $this->siswa1->id,
            'jumlah'            => 1,
            'tanggal_pinjam'    => now()->subDays(3)->toDateString(),
            'rencana_kembali'   => now()->subDay()->toDateString(), // Yesterday
            'keperluan'         => 'Testing overdue',
            'status'            => 'borrowed',
            'diserahkan_at'     => now()->subDays(3),
        ]);

        \App\Models\InventoryBorrowingUnit::create([
            'inventory_borrowing_id' => $borrowing->id,
            'inventory_unit_id'      => $unit->id,
            'status'                 => 'borrowed',
            'diserahkan_at'          => now()->subDays(3),
        ]);

        // Effective status should be 'overdue' (Belum Dikembalikan)
        $this->assertEquals('overdue', $borrowing->effective_status);
        $this->assertTrue($borrowing->is_overdue);

        // After return, effective status becomes 'returned' (Sudah Dikembalikan)
        $this->actingAs($this->kaprogPplgUser)->post(route('inventory.borrow.return', $borrowing->id), [
            'kondisi_sesudah'      => 'baik',
            'catatan_pengembalian' => 'Terlambat 1 hari karena kendala transport.',
        ]);

        $borrowing->refresh();
        $this->assertEquals('returned', $borrowing->effective_status);
        $this->assertFalse($borrowing->is_overdue);
    }

    /**
     * Skenario F: Permission and Department Scoping Security
     */
    public function test_skenario_f_permission_and_department_authorization(): void
    {
        $itemAnim = InventoryItem::create([
            'kode_barang'        => 'ANIM-TAB-001',
            'nama_barang'        => 'Wacom Cintiq Animasi',
            'jurusan_id'         => $this->jurusanAnim->id,
            'kategori'           => 'Desain',
            'lokasi_penyimpanan' => 'Studio Animasi',
            'total_unit'         => 2,
            'unit_tersedia'      => 2,
            'kondisi'            => 'baik',
            'status'             => 'tersedia',
        ]);
        $unitsAnim = $itemAnim->createUnits(2, 'baik');

        // 1. Kaprog PPLG tries to edit Animasi item -> 403 Forbidden
        $resp = $this->actingAs($this->kaprogPplgUser)->put(route('inventory.update', $itemAnim->id), [
            'nama_barang'        => 'Hacked Cintiq',
            'kategori'           => 'Desain',
            'lokasi_penyimpanan' => 'Studio Animasi',
        ]);
        $resp->assertForbidden();

        // 2. Kaprog PPLG tries to update unit of Animasi -> 403 Forbidden
        $respUnit = $this->actingAs($this->kaprogPplgUser)->put(route('inventory.units.update', $unitsAnim[0]->id), [
            'kondisi' => 'rusak_berat',
        ]);
        $respUnit->assertForbidden();

        // 3. Siswa tries to add physical unit -> 403 or redirect
        $respSiswa = $this->actingAs($this->siswaUser1)->post(route('inventory.units.store', $itemAnim->id), [
            'jumlah_unit' => 1,
            'kondisi'     => 'baik',
        ]);
        $respSiswa->assertRedirect(); // Blocked by role middleware
        $this->assertEquals(2, $itemAnim->units()->count());

        // 4. Guru biasa tries to approve borrowing -> 403
        $borrowing = InventoryBorrowing::create([
            'inventory_item_id' => $itemAnim->id,
            'user_id'           => $this->siswaUser1->id,
            'siswa_id'          => $this->siswa1->id,
            'jumlah'            => 1,
            'tanggal_pinjam'    => now()->toDateString(),
            'rencana_kembali'   => Carbon::tomorrow()->toDateString(),
            'keperluan'         => 'Tugas sketsa',
            'status'            => 'pending',
        ]);
        // Guru biasa (not kaprog of Animasi nor superadmin)
        $respApprove = $this->actingAs($this->guruUser)->post(route('inventory.borrow.approve', $borrowing->id));
        $this->assertEquals('pending', $borrowing->fresh()->status);
    }

    /**
     * Skenario G: Concurrent Borrowing and Race Condition Protection
     */
    public function test_skenario_g_concurrent_allocation_prevents_double_booking(): void
    {
        $item = InventoryItem::create([
            'kode_barang'        => 'PPLG-LTP-OMEN16',
            'nama_barang'        => 'OMEN 16',
            'jurusan_id'         => $this->jurusanPplg->id,
            'kategori'           => 'Laptop',
            'lokasi_penyimpanan' => 'Kantor PPLG',
            'total_unit'         => 1,
            'unit_tersedia'      => 1,
            'kondisi'            => 'baik',
            'status'             => 'tersedia',
        ]);
        $units = $item->createUnits(1, 'baik');
        $unit = $units[0];

        // Two students both submitted borrow request for the 1 remaining unit
        $borrowing1 = InventoryBorrowing::create([
            'inventory_item_id' => $item->id,
            'user_id'           => $this->siswaUser1->id,
            'siswa_id'          => $this->siswa1->id,
            'jumlah'            => 1,
            'tanggal_pinjam'    => now()->toDateString(),
            'rencana_kembali'   => Carbon::tomorrow()->toDateString(),
            'keperluan'         => 'Siswa 1',
            'status'            => 'pending',
        ]);

        $borrowing2 = InventoryBorrowing::create([
            'inventory_item_id' => $item->id,
            'user_id'           => $this->siswaUser2->id,
            'siswa_id'          => $this->siswa2->id,
            'jumlah'            => 1,
            'tanggal_pinjam'    => now()->toDateString(),
            'rencana_kembali'   => Carbon::tomorrow()->toDateString(),
            'keperluan'         => 'Siswa 2',
            'status'            => 'pending',
        ]);

        // First approval succeeds
        $resp1 = $this->actingAs($this->kaprogPplgUser)->post(route('inventory.borrow.approve', $borrowing1->id));
        $resp1->assertSessionHas('success');
        $this->assertEquals('approved', $borrowing1->fresh()->status);

        // Second approval must fail because the unit is already reserved
        $resp2 = $this->actingAs($this->kaprogPplgUser)->post(route('inventory.borrow.approve', $borrowing2->id));
        $resp2->assertSessionHas('error');
        $this->assertEquals('pending', $borrowing2->fresh()->status);

        // Assert unit is only allocated to borrowing 1
        $this->assertEquals(1, \App\Models\InventoryBorrowingUnit::where('inventory_unit_id', $unit->id)->count());
        $this->assertEquals($borrowing1->id, \App\Models\InventoryBorrowingUnit::where('inventory_unit_id', $unit->id)->first()->inventory_borrowing_id);
    }

    /**
     * Scan Lookup Endpoint Verification
     */
    public function test_scan_lookup_resolves_unit_and_action(): void
    {
        $item = InventoryItem::create([
            'kode_barang'        => 'PPLG-LTP-OMEN16',
            'nama_barang'        => 'OMEN 16',
            'jurusan_id'         => $this->jurusanPplg->id,
            'kategori'           => 'Laptop',
            'lokasi_penyimpanan' => 'Kantor PPLG',
            'total_unit'         => 1,
            'unit_tersedia'      => 1,
            'kondisi'            => 'baik',
            'status'             => 'tersedia',
        ]);
        $units = $item->createUnits(1, 'baik');
        $unit = $units[0];

        $resp = $this->actingAs($this->kaprogPplgUser)->getJson(route('inventory.scan.lookup', ['code' => $unit->kode_unit]));
        $resp->assertOk()
            ->assertJson([
                'success' => true,
                'type'    => 'unit',
                'unit'    => [
                    'kode_unit' => $unit->kode_unit,
                    'kondisi'   => 'baik',
                ],
            ]);
    }
}
