<?php

namespace Tests\Feature;

use App\Models\InventoryBorrowing;
use App\Models\InventoryItem;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaFaceBiometric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventorySystemTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
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
            'nama_kelas'   => '10 PPLG 1',
            'tingkat'      => '10',
            'jurusan_id'   => $this->jurusan->id,
            'tahun_ajaran' => '2026/2027',
            'jumlah_siswa' => 30,
        ]);

        $this->admin = User::create([
            'name'     => 'Admin Sarpras',
            'email'    => 'sarpras@smk.test',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $this->siswaUser = User::create([
            'name'     => 'Rizki Siswa',
            'email'    => 'rizki@smk.test',
            'password' => bcrypt('password'),
            'role'     => 'siswa',
        ]);

        $this->siswa = Siswa::create([
            'user_id'          => $this->siswaUser->id,
            'nama'             => 'Rizki Siswa',
            'nis'              => '10293847',
            'nisn'             => '0081234567',
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

    public function test_admin_can_create_inventory_item()
    {
        $response = $this->actingAs($this->admin)->post(route('inventory.store'), [
            'kode_barang'        => 'LAP-001',
            'nama_barang'        => 'Laptop Asus ROG Strix',
            'jurusan_id'         => $this->jurusan->id,
            'kategori'           => 'Elektronik & Komputer',
            'lokasi_penyimpanan' => 'Lab RPL 1',
            'deskripsi'          => 'Laptop gaming untuk lab coding',
            'total_unit'         => 5,
            'kondisi'            => 'baik',
        ]);

        $response->assertRedirect(route('inventory.index'));
        $this->assertDatabaseHas('inventory_items', [
            'kode_barang'   => 'LAP-001',
            'nama_barang'   => 'Laptop Asus ROG Strix',
            'unit_tersedia' => 5,
            'status'        => 'tersedia',
        ]);
    }

    public function test_user_can_submit_borrow_request()
    {
        $item = InventoryItem::create([
            'kode_barang'        => 'CAM-001',
            'nama_barang'        => 'Sony A6400 Camera',
            'jurusan_id'         => $this->jurusan->id,
            'kategori'           => 'Multimedia & Visual',
            'lokasi_penyimpanan' => 'Lab BCF',
            'total_unit'         => 3,
            'unit_tersedia'      => 3,
            'kondisi'            => 'baik',
            'status'             => 'tersedia',
        ]);

        $response = $this->actingAs($this->siswaUser)->post(route('inventory.borrow.request'), [
            'inventory_item_id' => $item->id,
            'jumlah'            => 1,
            'rencana_kembali'   => now()->addDays(2)->toDateString(),
            'keperluan'         => 'Praktik video documenter',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('inventory_borrowings', [
            'inventory_item_id' => $item->id,
            'user_id'           => $this->siswaUser->id,
            'siswa_id'          => $this->siswa->id,
            'jumlah'            => 1,
            'status'            => 'pending',
        ]);
    }

    public function test_approval_decrements_available_units()
    {
        $item = InventoryItem::create([
            'kode_barang'        => 'SDR-001',
            'nama_barang'        => 'Soldering Station Goot',
            'jurusan_id'         => $this->jurusan->id,
            'kategori'           => 'Mekatronika & Alat Ukur',
            'lokasi_penyimpanan' => 'Bengkel Elektro',
            'total_unit'         => 2,
            'unit_tersedia'      => 2,
            'kondisi'            => 'baik',
            'status'             => 'tersedia',
        ]);

        $borrowing = InventoryBorrowing::create([
            'inventory_item_id' => $item->id,
            'user_id'           => $this->siswaUser->id,
            'siswa_id'          => $this->siswa->id,
            'jumlah'            => 2,
            'tanggal_pinjam'    => now()->toDateString(),
            'rencana_kembali'   => now()->addDays(1)->toDateString(),
            'keperluan'         => 'Praktik soldering IoT',
            'status'            => 'pending',
            'kondisi_sebelum'   => 'baik',
        ]);

        $response = $this->actingAs($this->admin)->post(route('inventory.borrow.approve', $borrowing->id));
        $response->assertSessionHas('success');

        $borrowing->refresh();
        $item->refresh();

        $this->assertEquals('borrowed', $borrowing->status);
        $this->assertEquals(0, $item->unit_tersedia);
        $this->assertEquals('dipinjam', $item->status);
    }

    public function test_return_restores_units_and_inspects_condition()
    {
        $item = InventoryItem::create([
            'kode_barang'        => 'VR-001',
            'nama_barang'        => 'Oculus Quest 2',
            'jurusan_id'         => $this->jurusan->id,
            'kategori'           => 'Elektronik & Komputer',
            'lokasi_penyimpanan' => 'Lab Game',
            'total_unit'         => 1,
            'unit_tersedia'      => 0,
            'kondisi'            => 'baik',
            'status'             => 'dipinjam',
        ]);

        $borrowing = InventoryBorrowing::create([
            'inventory_item_id' => $item->id,
            'user_id'           => $this->siswaUser->id,
            'siswa_id'          => $this->siswa->id,
            'jumlah'            => 1,
            'tanggal_pinjam'    => now()->subDays(2)->toDateString(),
            'rencana_kembali'   => now()->toDateString(),
            'keperluan'         => 'Testing game Unity VR',
            'status'            => 'borrowed',
            'kondisi_sebelum'   => 'baik',
        ]);

        $response = $this->actingAs($this->admin)->post(route('inventory.borrow.return', $borrowing->id), [
            'kondisi_sesudah'      => 'baik',
            'catatan_pengembalian' => 'Alat dikembalikan tepat waktu dan lengkap kabel charger.',
        ]);

        $response->assertSessionHas('success');
        $borrowing->refresh();
        $item->refresh();

        $this->assertEquals('returned', $borrowing->status);
        $this->assertEquals(1, $item->unit_tersedia);
        $this->assertEquals('tersedia', $item->status);
    }

    public function test_severe_damage_on_return_quarantines_item()
    {
        $item = InventoryItem::create([
            'kode_barang'        => 'DRN-001',
            'nama_barang'        => 'Drone DJI Mini 2',
            'jurusan_id'         => $this->jurusan->id,
            'kategori'           => 'Multimedia & Visual',
            'lokasi_penyimpanan' => 'Lab BCF',
            'total_unit'         => 1,
            'unit_tersedia'      => 0,
            'kondisi'            => 'baik',
            'status'             => 'dipinjam',
        ]);

        $borrowing = InventoryBorrowing::create([
            'inventory_item_id' => $item->id,
            'user_id'           => $this->siswaUser->id,
            'siswa_id'          => $this->siswa->id,
            'jumlah'            => 1,
            'tanggal_pinjam'    => now()->subDays(1)->toDateString(),
            'rencana_kembali'   => now()->toDateString(),
            'keperluan'         => 'Syuting aerial sekolah',
            'status'            => 'borrowed',
            'kondisi_sebelum'   => 'baik',
        ]);

        $response = $this->actingAs($this->admin)->post(route('inventory.borrow.return', $borrowing->id), [
            'kondisi_sesudah'      => 'rusak_berat',
            'catatan_pengembalian' => 'Baling-baling patah dan gimbal error akibat benturan.',
        ]);

        $response->assertSessionHas('success');
        $borrowing->refresh();
        $item->refresh();

        $this->assertEquals('returned', $borrowing->status);
        $this->assertEquals('rusak_berat', $item->kondisi);
        $this->assertEquals('maintenance', $item->status);
        $this->assertEquals(0, $item->unit_tersedia); // Not restored to loan pool
    }

    public function test_cannot_borrow_unavailable_or_damaged_item()
    {
        $item = InventoryItem::create([
            'kode_barang'        => 'OSC-001',
            'nama_barang'        => 'Oscilloscope Rigol',
            'jurusan_id'         => $this->jurusan->id,
            'kategori'           => 'Mekatronika & Alat Ukur',
            'lokasi_penyimpanan' => 'Bengkel Elektro',
            'total_unit'         => 1,
            'unit_tersedia'      => 0,
            'kondisi'            => 'rusak_berat',
            'status'             => 'maintenance',
        ]);

        $response = $this->actingAs($this->siswaUser)->post(route('inventory.borrow.request'), [
            'inventory_item_id' => $item->id,
            'jumlah'            => 1,
            'rencana_kembali'   => now()->addDay()->toDateString(),
            'keperluan'         => 'Pengujian gelombang PWM',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('inventory_borrowings', [
            'inventory_item_id' => $item->id,
            'user_id'           => $this->siswaUser->id,
        ]);
    }
}
