<?php

namespace Tests\Feature;

use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use App\Models\DisciplineRecordItem;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisciplineMasterAndMultiItemRecordsTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $guru;
    private $siswaUser;
    private $siswa;
    private $kelas;
    private $jurusan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name'     => 'Super Administrator',
            'email'    => 'admin@myskanic.test',
            'password' => bcrypt('password'),
            'role'     => 'superadmin',
        ]);

        $this->guru = User::create([
            'name'     => 'Guru Pembina BK',
            'email'    => 'gurubk@myskanic.test',
            'password' => bcrypt('password'),
            'role'     => 'guru',
        ]);

        $this->jurusan = Jurusan::create([
            'kode_jurusan' => 'PPLG',
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas'    => 'XII PPLG 1',
            'tingkat'       => '12',
            'jurusan_id'    => $this->jurusan->id,
            'tahun_ajaran'  => '2026/2027',
            'jumlah_siswa'  => 32,
        ]);

        $this->siswaUser = User::create([
            'name'     => 'Azura Siswa',
            'email'    => 'azura@myskanic.test',
            'password' => bcrypt('password'),
            'role'     => 'siswa',
        ]);

        $this->siswa = Siswa::create([
            'user_id'             => $this->siswaUser->id,
            'nama'                => 'Azura Siswa',
            'nis'                 => '2122001',
            'nisn'                => '0061234567',
            'kelas_id'            => $this->kelas->id,
            'jurusan_id'          => $this->jurusan->id,
            'biometric_status'    => 'enrolled',
            'is_biometric_exempt' => true,
        ]);
    }

    public function test_admin_can_view_master_data_index_and_stats()
    {
        DisciplineCategory::create([
            'nama'         => 'Terlambat Masuk',
            'tipe'         => 'pelanggaran',
            'default_poin' => -5,
            'is_active'    => true,
        ]);

        DisciplineCategory::create([
            'nama'         => 'Juara LKS Kota',
            'tipe'         => 'prestasi',
            'default_poin' => 50,
            'is_active'    => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('discipline.master.index'));

        $response->assertOk();
        $response->assertSee('Master Pelanggaran & Prestasi');
        $response->assertSee('Terlambat Masuk');
        $response->assertSee('Juara LKS Kota');
    }

    public function test_admin_can_create_violation_master_with_negative_point()
    {
        $response = $this->actingAs($this->admin)->post(route('discipline.master.store'), [
            'nama'         => 'Merokok di Kantin',
            'tipe'         => 'pelanggaran',
            'default_poin' => -30,
            'deskripsi'    => 'Membawa rokok atau vape di area kantin sekolah.',
            'is_active'    => 1,
        ]);

        $response->assertRedirect(route('discipline.master.index'));
        $this->assertDatabaseHas('discipline_categories', [
            'nama'         => 'Merokok di Kantin',
            'tipe'         => 'pelanggaran',
            'default_poin' => -30,
            'is_active'    => true,
        ]);
    }

    public function test_admin_can_create_achievement_master_with_positive_point()
    {
        $response = $this->actingAs($this->admin)->post(route('discipline.master.store'), [
            'nama'         => 'Juara 1 Hackathon Nasional',
            'tipe'         => 'prestasi',
            'default_poin' => 100,
            'deskripsi'    => 'Juara 1 lomba pemrograman hackathon nasional.',
            'is_active'    => 1,
        ]);

        $response->assertRedirect(route('discipline.master.index'));
        $this->assertDatabaseHas('discipline_categories', [
            'nama'         => 'Juara 1 Hackathon Nasional',
            'tipe'         => 'prestasi',
            'default_poin' => 100,
            'is_active'    => true,
        ]);
    }

    public function test_admin_can_create_master_with_zero_point()
    {
        $response = $this->actingAs($this->admin)->post(route('discipline.master.store'), [
            'nama'         => 'Konseling Netral Perilaku',
            'tipe'         => 'pelanggaran',
            'default_poin' => 0,
            'deskripsi'    => 'Peringatan lisan tanpa pemotongan poin.',
            'is_active'    => 1,
        ]);

        $response->assertRedirect(route('discipline.master.index'));
        $this->assertDatabaseHas('discipline_categories', [
            'nama'         => 'Konseling Netral Perilaku',
            'default_poin' => 0,
        ]);
    }

    public function test_admin_can_update_master_standard_name_and_point()
    {
        $category = DisciplineCategory::create([
            'nama'         => 'Atribut Tidak Lengkap',
            'tipe'         => 'pelanggaran',
            'default_poin' => -5,
            'is_active'    => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('discipline.master.update', $category->id), [
            'nama'         => 'Atribut Seragam Tidak Lengkap / Rusak',
            'tipe'         => 'pelanggaran',
            'default_poin' => -10,
            'deskripsi'    => 'Tidak mengenakan dasi, gesper, atau topi saat upacara.',
            'is_active'    => 1,
        ]);

        $response->assertRedirect(route('discipline.master.index'));
        $this->assertDatabaseHas('discipline_categories', [
            'id'           => $category->id,
            'nama'         => 'Atribut Seragam Tidak Lengkap / Rusak',
            'default_poin' => -10,
        ]);
    }

    public function test_admin_can_toggle_master_active_status()
    {
        $category = DisciplineCategory::create([
            'nama'         => 'Kategori Uji Coba',
            'tipe'         => 'pelanggaran',
            'default_poin' => -5,
            'is_active'    => true,
        ]);

        // Toggle to inactive
        $response = $this->actingAs($this->admin)->post(route('discipline.master.toggle-status', $category->id));
        $response->assertRedirect(route('discipline.master.index'));
        $this->assertFalse($category->fresh()->is_active);

        // Toggle back to active
        $response = $this->actingAs($this->admin)->post(route('discipline.master.toggle-status', $category->id));
        $this->assertTrue($category->fresh()->is_active);
    }

    public function test_cannot_delete_master_if_referenced_in_records()
    {
        $category = DisciplineCategory::create([
            'nama'         => 'Terlambat Masuk',
            'tipe'         => 'pelanggaran',
            'default_poin' => -5,
            'is_active'    => true,
        ]);

        $record = DisciplineRecord::create([
            'siswa_id'               => $this->siswa->id,
            'discipline_category_id' => $category->id,
            'tipe'                   => 'pelanggaran',
            'tanggal'                => now()->toDateString(),
            'judul'                  => 'Terlambat 15 menit',
            'deskripsi'              => 'Terlambat upacara',
            'poin'                   => -5,
        ]);

        DisciplineRecordItem::create([
            'discipline_record_id'   => $record->id,
            'discipline_category_id' => $category->id,
            'nama_standar'           => $category->nama,
            'tipe'                   => 'pelanggaran',
            'poin'                   => -5,
        ]);

        // Try to delete master
        $response = $this->actingAs($this->admin)->delete(route('discipline.master.destroy', $category->id));

        $response->assertRedirect(route('discipline.master.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('discipline_categories', ['id' => $category->id]);
    }

    public function test_can_delete_unused_master_standard()
    {
        $category = DisciplineCategory::create([
            'nama'         => 'Standar Eksperimental',
            'tipe'         => 'prestasi',
            'default_poin' => 15,
            'is_active'    => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('discipline.master.destroy', $category->id));

        $response->assertRedirect(route('discipline.master.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('discipline_categories', ['id' => $category->id]);
    }

    public function test_multi_item_violation_recording_calculates_total_points_atomically()
    {
        $cat1 = DisciplineCategory::create([
            'nama'         => 'Atribut seragam tidak lengkap',
            'tipe'         => 'pelanggaran',
            'default_poin' => -5,
            'is_active'    => true,
        ]);

        $cat2 = DisciplineCategory::create([
            'nama'         => 'Terlambat masuk sekolah',
            'tipe'         => 'pelanggaran',
            'default_poin' => -5,
            'is_active'    => true,
        ]);

        $cat3 = DisciplineCategory::create([
            'nama'         => 'Meninggalkan KBM tanpa keterangan',
            'tipe'         => 'pelanggaran',
            'default_poin' => -15,
            'is_active'    => true,
        ]);

        $response = $this->actingAs($this->guru)->post(route('discipline.store'), [
            'siswa_id'  => $this->siswa->id,
            'tanggal'   => now()->toDateString(),
            'judul'     => 'Pelanggaran Tata Tertib Hari Senin',
            'deskripsi' => 'Siswa ditemukan terlambat, tidak memakai dasi, dan meninggalkan jam pelajaran ke-3.',
            'items'     => [
                ['category_id' => $cat1->id, 'keterangan' => 'Tidak pakai dasi'],
                ['category_id' => $cat2->id, 'keterangan' => 'Terlambat 20 menit'],
                ['category_id' => $cat3->id, 'keterangan' => 'Bolos jam matematika'],
            ],
        ]);

        $response->assertRedirect(route('discipline.index'));
        $response->assertSessionHas('success');

        // Total point must be -5 + -5 + -15 = -25
        $this->assertDatabaseHas('discipline_records', [
            'siswa_id' => $this->siswa->id,
            'tipe'     => 'pelanggaran',
            'poin'     => -25,
            'judul'    => 'Pelanggaran Tata Tertib Hari Senin',
        ]);

        $record = DisciplineRecord::where('judul', 'Pelanggaran Tata Tertib Hari Senin')->first();
        $this->assertNotNull($record);
        $this->assertCount(3, $record->items);

        $this->assertDatabaseHas('discipline_record_items', [
            'discipline_record_id'   => $record->id,
            'discipline_category_id' => $cat1->id,
            'nama_standar'           => 'Atribut seragam tidak lengkap',
            'poin'                   => -5,
            'keterangan'             => 'Tidak pakai dasi',
        ]);
        $this->assertDatabaseHas('discipline_record_items', [
            'discipline_record_id'   => $record->id,
            'discipline_category_id' => $cat3->id,
            'nama_standar'           => 'Meninggalkan KBM tanpa keterangan',
            'poin'                   => -15,
        ]);
    }

    public function test_multi_item_achievement_recording_calculates_merit_points()
    {
        $cat1 = DisciplineCategory::create([
            'nama'         => 'Juara LKS Kota',
            'tipe'         => 'prestasi',
            'default_poin' => 50,
            'is_active'    => true,
        ]);

        $cat2 = DisciplineCategory::create([
            'nama'         => 'Pengurus Aktif OSIS',
            'tipe'         => 'prestasi',
            'default_poin' => 20,
            'is_active'    => true,
        ]);

        $response = $this->actingAs($this->guru)->post(route('discipline.store'), [
            'siswa_id'  => $this->siswa->id,
            'tanggal'   => now()->toDateString(),
            'judul'     => 'Apresiasi Prestasi dan Keaktifan Organisasi',
            'deskripsi' => 'Meraih juara 1 LKS Kota dan menjabat sebagai sekretaris OSIS.',
            'items'     => [
                ['category_id' => $cat1->id, 'keterangan' => 'Medali emas LKS'],
                ['category_id' => $cat2->id, 'keterangan' => 'Sekretaris OSIS 2026'],
            ],
        ]);

        $response->assertRedirect(route('discipline.index'));

        // Total point must be 50 + 20 = 70
        $this->assertDatabaseHas('discipline_records', [
            'siswa_id' => $this->siswa->id,
            'tipe'     => 'prestasi',
            'poin'     => 70,
        ]);

        $record = DisciplineRecord::where('judul', 'Apresiasi Prestasi dan Keaktifan Organisasi')->first();
        $this->assertCount(2, $record->items);
    }

    public function test_rejects_mixing_violations_and_achievements_in_single_record()
    {
        $pelanggaran = DisciplineCategory::create([
            'nama'         => 'Terlambat Masuk',
            'tipe'         => 'pelanggaran',
            'default_poin' => -5,
            'is_active'    => true,
        ]);

        $prestasi = DisciplineCategory::create([
            'nama'         => 'Juara LKS',
            'tipe'         => 'prestasi',
            'default_poin' => 50,
            'is_active'    => true,
        ]);

        $response = $this->actingAs($this->guru)->post(route('discipline.store'), [
            'siswa_id'  => $this->siswa->id,
            'tanggal'   => now()->toDateString(),
            'judul'     => 'Campuran Tidak Sah',
            'deskripsi' => 'Mencoba mencampur pelanggaran dan prestasi.',
            'items'     => [
                ['category_id' => $pelanggaran->id],
                ['category_id' => $prestasi->id],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseMissing('discipline_records', [
            'judul' => 'Campuran Tidak Sah',
        ]);
    }

    public function test_rejects_inactive_master_category_for_new_records()
    {
        $cat = DisciplineCategory::create([
            'nama'         => 'Kategori Lama Nonaktif',
            'tipe'         => 'pelanggaran',
            'default_poin' => -10,
            'is_active'    => false, // Inactive
        ]);

        $response = $this->actingAs($this->guru)->post(route('discipline.store'), [
            'siswa_id'  => $this->siswa->id,
            'tanggal'   => now()->toDateString(),
            'judul'     => 'Uji Kategori Nonaktif',
            'deskripsi' => 'Deskripsi',
            'items'     => [
                ['category_id' => $cat->id],
            ],
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('discipline_records', [
            'judul' => 'Uji Kategori Nonaktif',
        ]);
    }

    public function test_rejects_invalid_master_category_id()
    {
        $response = $this->actingAs($this->guru)->post(route('discipline.store'), [
            'siswa_id'  => $this->siswa->id,
            'tanggal'   => now()->toDateString(),
            'judul'     => 'Uji ID Palsu',
            'deskripsi' => 'Deskripsi',
            'items'     => [
                ['category_id' => 99999],
            ],
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_history_snapshot_preserves_original_points_when_master_is_updated()
    {
        // 1. Create master with -5 points
        $cat = DisciplineCategory::create([
            'nama'         => 'Atribut Tidak Lengkap',
            'tipe'         => 'pelanggaran',
            'default_poin' => -5,
            'is_active'    => true,
        ]);

        // 2. Record infraction on 13 Nov
        $this->actingAs($this->guru)->post(route('discipline.store'), [
            'siswa_id'  => $this->siswa->id,
            'tanggal'   => '2026-11-13',
            'judul'     => 'Kasus Tanggal 13 Nov',
            'deskripsi' => 'Catatan awal',
            'items'     => [
                ['category_id' => $cat->id],
            ],
        ]);

        $firstRecord = DisciplineRecord::where('judul', 'Kasus Tanggal 13 Nov')->first();
        $this->assertEquals(-5, $firstRecord->poin);
        $this->assertEquals(-5, $firstRecord->items->first()->poin);

        // 3. Admin updates master points from -5 to -10 on 20 Nov
        $this->actingAs($this->admin)->put(route('discipline.master.update', $cat->id), [
            'nama'         => 'Atribut Tidak Lengkap (Revisi Berat)',
            'tipe'         => 'pelanggaran',
            'default_poin' => -10,
            'is_active'    => 1,
        ]);

        // 4. Assert first record STILL HAS original snapshot -5 points!
        $firstRecord->refresh();
        $this->assertEquals(-5, $firstRecord->poin);
        $this->assertEquals(-5, $firstRecord->items->first()->poin);
        $this->assertEquals('Atribut Tidak Lengkap', $firstRecord->items->first()->nama_standar);

        // 5. Create new record; it must use updated -10 points
        $this->actingAs($this->guru)->post(route('discipline.store'), [
            'siswa_id'  => $this->siswa->id,
            'tanggal'   => now()->toDateString(),
            'judul'     => 'Kasus Baru Tanggal Hari Ini',
            'deskripsi' => 'Catatan setelah perubahan master',
            'items'     => [
                ['category_id' => $cat->id],
            ],
        ]);

        $secondRecord = DisciplineRecord::where('judul', 'Kasus Baru Tanggal Hari Ini')->first();
        $this->assertEquals(-10, $secondRecord->poin);
        $this->assertEquals(-10, $secondRecord->items->first()->poin);
    }

    public function test_update_discipline_record_replaces_items_and_recalculates_points()
    {
        $cat1 = DisciplineCategory::create([
            'nama'         => 'Pelanggaran 1',
            'tipe'         => 'pelanggaran',
            'default_poin' => -5,
            'is_active'    => true,
        ]);

        $cat2 = DisciplineCategory::create([
            'nama'         => 'Pelanggaran 2',
            'tipe'         => 'pelanggaran',
            'default_poin' => -15,
            'is_active'    => true,
        ]);

        $record = DisciplineRecord::create([
            'siswa_id'  => $this->siswa->id,
            'tipe'      => 'pelanggaran',
            'tanggal'   => now()->toDateString(),
            'judul'     => 'Judul Lama',
            'deskripsi' => 'Deskripsi lama',
            'poin'      => -5,
        ]);

        $record->items()->create([
            'discipline_category_id' => $cat1->id,
            'nama_standar'           => $cat1->nama,
            'tipe'                   => 'pelanggaran',
            'poin'                   => -5,
        ]);

        // Update: replace with two items (cat1 + cat2 = -20)
        $response = $this->actingAs($this->guru)->put(route('discipline.update', $record->id), [
            'tanggal'   => now()->toDateString(),
            'judul'     => 'Judul Baru Diperbarui',
            'deskripsi' => 'Deskripsi baru',
            'items'     => [
                ['category_id' => $cat1->id, 'keterangan' => 'Keterangan 1'],
                ['category_id' => $cat2->id, 'keterangan' => 'Keterangan 2'],
            ],
        ]);

        $response->assertRedirect(route('discipline.index'));
        $record->refresh();
        $this->assertEquals('Judul Baru Diperbarui', $record->judul);
        $this->assertEquals(-20, $record->poin);
        $this->assertCount(2, $record->items);
    }

    public function test_delete_discipline_record_cleans_up_child_items()
    {
        $cat = DisciplineCategory::create([
            'nama'         => 'Pelanggaran',
            'tipe'         => 'pelanggaran',
            'default_poin' => -5,
            'is_active'    => true,
        ]);

        $record = DisciplineRecord::create([
            'siswa_id'  => $this->siswa->id,
            'tipe'      => 'pelanggaran',
            'tanggal'   => now()->toDateString(),
            'judul'     => 'Catatan Akan Dihapus',
            'deskripsi' => 'Deskripsi',
            'poin'      => -5,
        ]);

        $item = $record->items()->create([
            'discipline_category_id' => $cat->id,
            'nama_standar'           => $cat->nama,
            'tipe'                   => 'pelanggaran',
            'poin'                   => -5,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('discipline.destroy', $record->id));

        $response->assertRedirect(route('discipline.index'));
        $this->assertDatabaseMissing('discipline_records', ['id' => $record->id]);
        $this->assertDatabaseMissing('discipline_record_items', ['id' => $item->id]);
    }

    public function test_options_discipline_categories_supports_server_side_pagination_search_and_type_filter()
    {
        for ($i = 1; $i <= 20; $i++) {
            DisciplineCategory::create([
                'nama'         => "Pelanggaran Khusus {$i}",
                'tipe'         => 'pelanggaran',
                'default_poin' => -5,
                'is_active'    => true,
            ]);
        }

        DisciplineCategory::create([
            'nama'         => 'Prestasi Luar Biasa',
            'tipe'         => 'prestasi',
            'default_poin' => 50,
            'is_active'    => true,
        ]);

        // 1. Pagination page 1 limit 5
        $response = $this->actingAs($this->guru)->getJson(route('options.select.discipline-categories', [
            'per_page' => 5,
            'page'     => 1,
        ]));

        $response->assertOk();
        $response->assertJsonPath('pagination.per_page', 5);
        $response->assertJsonPath('pagination.current_page', 1);
        $response->assertJsonPath('pagination.has_more', true);

        // 2. Filter by type: only prestasi
        $responsePrestasi = $this->actingAs($this->guru)->getJson(route('options.select.discipline-categories', [
            'tipe' => 'prestasi',
        ]));
        $responsePrestasi->assertOk();
        $this->assertGreaterThanOrEqual(1, count($responsePrestasi->json('data')));
        $this->assertTrue(collect($responsePrestasi->json('data'))->contains('nama', 'Prestasi Luar Biasa'));

        // 3. Search query
        $responseSearch = $this->actingAs($this->guru)->getJson(route('options.select.discipline-categories', [
            'q' => 'Khusus 12',
        ]));
        $responseSearch->assertOk();
        $this->assertEquals('Pelanggaran Khusus 12', $responseSearch->json('data.0.nama'));
    }

    public function test_student_portal_calculates_score_with_multi_item_records()
    {
        // Infraction record: total -20
        $rec1 = DisciplineRecord::create([
            'siswa_id'  => $this->siswa->id,
            'tipe'      => 'pelanggaran',
            'tanggal'   => now()->subDays(3)->toDateString(),
            'judul'     => 'Pelanggaran Multi-Item',
            'deskripsi' => 'Terlambat dan atribut',
            'poin'      => -20,
            'status'    => 'verified',
        ]);
        $rec1->items()->create(['nama_standar' => 'Terlambat', 'tipe' => 'pelanggaran', 'poin' => -5]);
        $rec1->items()->create(['nama_standar' => 'Bolos', 'tipe' => 'pelanggaran', 'poin' => -15]);

        // Achievement record: total +40
        $rec2 = DisciplineRecord::create([
            'siswa_id'  => $this->siswa->id,
            'tipe'      => 'prestasi',
            'tanggal'   => now()->subDay()->toDateString(),
            'judul'     => 'Prestasi Kejuaraan',
            'deskripsi' => 'LKS',
            'poin'      => 40,
            'status'    => 'verified',
        ]);
        $rec2->items()->create(['nama_standar' => 'Juara LKS', 'tipe' => 'prestasi', 'poin' => 40]);

        // Formula: 100 - Pelanggaran (20) + (Prestasi (40) / 2 = 20) = 100
        $response = $this->actingAs($this->siswaUser)->get(route('discipline.student.portal'));

        $response->assertOk();
        $response->assertSee('Azura Siswa');
        $response->assertSee('-20 Poin');
        $response->assertSee('+40 Poin');
        $response->assertSee('100'); // final score
    }

    public function test_non_authorized_user_cannot_create_or_modify_master_data()
    {
        // Siswa tries to post to master store (blocked by role middleware)
        $response = $this->actingAs($this->siswaUser)->post(route('discipline.master.store'), [
            'nama'         => 'Hacking Master',
            'tipe'         => 'prestasi',
            'default_poin' => 100,
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }
}
