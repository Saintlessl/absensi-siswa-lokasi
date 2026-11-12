<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\PklDudiPartner;
use App\Models\PklPlacement;
use App\Models\Siswa;
use App\Models\StudentEnrollment;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchableSelectAndMultiPlottingTest extends TestCase
{
    use RefreshDatabase;

    protected $pplg;
    protected $rombel12;
    protected $admin;
    protected $ta;
    protected $siswaList = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->pplg = Jurusan::create([
            'kode_jurusan' => 'PPLG',
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'is_active' => true,
        ]);

        $this->ta = TahunAjaran::create([
            'nama' => '2026/2027 Ganjil',
            'tahun_mulai' => 2026,
            'tahun_selesai' => 2027,
            'is_active' => true,
            'status' => 'aktif',
            'tanggal_mulai' => '2026-07-15',
            'tanggal_selesai' => '2026-12-20',
        ]);

        $this->rombel12 = Kelas::create([
            'nama_kelas' => 'XII PPLG 1',
            'jurusan_id' => $this->pplg->id,
            'tingkat' => 12,
            'nomor_rombel' => 1,
            'tahun_ajaran' => '2026/2027',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Admin PKL',
            'email' => 'admin-pkl@myskanic.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => 1,
        ]);

        // Create 5 active students in class XII PPLG 1
        for ($i = 1; $i <= 5; $i++) {
            $user = User::create([
                'name' => "Siswa {$i} PPLG",
                'email' => "siswa{$i}@myskanic.test",
                'password' => bcrypt('password'),
                'role' => 'siswa',
                'is_active' => 1,
            ]);

            $siswa = Siswa::create([
                'user_id' => $user->id,
                'nis' => "2400{$i}",
                'nisn' => "007000000{$i}",
                'nama' => "Siswa {$i} PPLG",
                'jenis_kelamin' => 'L',
                'status' => 'aktif',
                'kelas_id' => $this->rombel12->id,
            ]);

            StudentEnrollment::create([
                'siswa_id' => $siswa->id,
                'tahun_ajaran_id' => $this->ta->id,
                'kelas_id' => $this->rombel12->id,
                'jurusan_id' => $this->pplg->id,
                'tingkat' => 12,
                'tahun_ajaran' => '2026/2027',
                'is_active' => true,
            ]);

            $this->siswaList[] = $siswa;
        }
    }

    protected function createPartner(string $nama, int $kuota = 10): PklDudiPartner
    {
        return PklDudiPartner::create([
            'nama_dudi' => $nama,
            'bidang_industri' => 'IT & Software',
            'alamat' => 'Jl. Teknologi No. 1',
            'kota' => 'Bogor',
            'kontak_person' => 'HRD',
            'jurusan_id' => $this->pplg->id,
            'kuota_siswa' => $kuota,
            'status' => 'aktif',
        ]);
    }

    /**
     * Test 1: Bulk multi-select student placement saves all students in transaction
     */
    public function test_bulk_student_placement_creates_records_for_all_selected_students(): void
    {
        $partner = $this->createPartner('PT Mitra Digital Unggul', 10);
        $selectedIds = [$this->siswaList[0]->id, $this->siswaList[1]->id, $this->siswaList[2]->id];

        $response = $this->actingAs($this->admin)->post(route('pkl.placements.store'), [
            'jurusan_id'          => $this->pplg->id,
            'kelas_id'            => $this->rombel12->id,
            'siswa_ids'           => $selectedIds,
            'pkl_dudi_partner_id' => $partner->id,
            'tanggal_mulai'       => '2026-08-01',
            'tanggal_selesai'     => '2027-01-31',
            'catatan'             => 'Plotting Multi-Siswa Batch 1',
        ]);

        $response->assertRedirect(route('pkl.placements.index'));
        $response->assertSessionHas('success', function ($msg) {
            return str_contains($msg, '3 siswa');
        });

        foreach ($selectedIds as $sid) {
            $this->assertDatabaseHas('pkl_placements', [
                'siswa_id'            => $sid,
                'pkl_dudi_partner_id' => $partner->id,
                'status'              => 'active',
                'catatan'             => 'Plotting Multi-Siswa Batch 1',
            ]);
        }
        $this->assertDatabaseCount('pkl_placements', 3);
    }

    /**
     * Test 2: Multi-select quota check rejects entire batch if exceeds partner capacity
     */
    public function test_bulk_placement_rejected_if_batch_exceeds_partner_quota(): void
    {
        $partner = $this->createPartner('PT Kuota Terbatas', 2); // Quota is only 2
        $selectedIds = [$this->siswaList[0]->id, $this->siswaList[1]->id, $this->siswaList[2]->id]; // Trying to place 3

        $response = $this->actingAs($this->admin)->post(route('pkl.placements.store'), [
            'jurusan_id'          => $this->pplg->id,
            'kelas_id'            => $this->rombel12->id,
            'siswa_ids'           => $selectedIds,
            'pkl_dudi_partner_id' => $partner->id,
            'tanggal_mulai'       => '2026-08-01',
            'tanggal_selesai'     => '2027-01-31',
        ]);

        $response->assertSessionHas('error', function ($msg) {
            return str_contains($msg, 'Kapasitas mitra penuh');
        });

        $this->assertDatabaseCount('pkl_placements', 0);
    }

    /**
     * Test 3: Transaction rolls back completely if one student has conflict
     */
    public function test_bulk_placement_rolls_back_if_one_student_has_active_placement(): void
    {
        $partner1 = $this->createPartner('PT Mitra A', 5);
        $partner2 = $this->createPartner('PT Mitra B', 5);

        // Siswa 0 already placed in Mitra A
        PklPlacement::create([
            'siswa_id'            => $this->siswaList[0]->id,
            'pkl_dudi_partner_id' => $partner1->id,
            'tanggal_mulai'       => '2026-08-01',
            'tanggal_selesai'     => '2027-01-31',
            'status'              => 'active',
        ]);

        // Now attempt to place Siswa 0, Siswa 1, Siswa 2 in Mitra B
        $selectedIds = [$this->siswaList[1]->id, $this->siswaList[0]->id, $this->siswaList[2]->id];

        $response = $this->actingAs($this->admin)->post(route('pkl.placements.store'), [
            'jurusan_id'          => $this->pplg->id,
            'kelas_id'            => $this->rombel12->id,
            'siswa_ids'           => $selectedIds,
            'pkl_dudi_partner_id' => $partner2->id,
            'tanggal_mulai'       => '2026-08-01',
            'tanggal_selesai'     => '2027-01-31',
        ]);

        $response->assertSessionHas('error', function ($msg) {
            return str_contains($msg, 'sudah memiliki penempatan PKL aktif');
        });

        // Mitra B should have 0 placements, and only the 1 existing placement in Mitra A remains
        $this->assertDatabaseCount('pkl_placements', 1);
        $this->assertDatabaseMissing('pkl_placements', [
            'pkl_dudi_partner_id' => $partner2->id,
        ]);
    }

    /**
     * Test 4: Server-side pagination and search on pkl.options.siswa
     */
    public function test_options_siswa_supports_server_side_pagination_and_search(): void
    {
        // 1. Pagination page 1 with per_page = 2
        $resp = $this->actingAs($this->admin)->getJson(
            route('pkl.options.siswa', ['kelas_id' => $this->rombel12->id, 'page' => 1, 'per_page' => 2])
        );

        $resp->assertOk();
        $resp->assertJsonStructure([
            'data',
            'pagination' => ['current_page', 'per_page', 'total', 'last_page', 'has_more'],
        ]);

        $this->assertCount(2, $resp->json('data'));
        $this->assertSame(1, $resp->json('pagination.current_page'));
        $this->assertSame(5, $resp->json('pagination.total'));
        $this->assertSame(3, $resp->json('pagination.last_page'));
        $this->assertTrue($resp->json('pagination.has_more'));

        // 2. Pagination page 2
        $respPage2 = $this->actingAs($this->admin)->getJson(
            route('pkl.options.siswa', ['kelas_id' => $this->rombel12->id, 'page' => 2, 'per_page' => 2])
        );
        $respPage2->assertOk();
        $this->assertCount(2, $respPage2->json('data'));
        $this->assertSame(2, $respPage2->json('pagination.current_page'));

        // 3. Search by NIS
        $respSearch = $this->actingAs($this->admin)->getJson(
            route('pkl.options.siswa', ['kelas_id' => $this->rombel12->id, 'q' => '24003', 'page' => 1])
        );
        $respSearch->assertOk();
        $this->assertCount(1, $respSearch->json('data'));
        $this->assertSame('24003', $respSearch->json('data.0.nis'));
        $this->assertSame($this->siswaList[2]->id, $respSearch->json('data.0.id'));

        // 4. Resolve selected_ids
        $respResolve = $this->actingAs($this->admin)->getJson(
            route('pkl.options.siswa', [
                'kelas_id'     => $this->rombel12->id,
                'selected_ids' => "{$this->siswaList[0]->id},{$this->siswaList[3]->id}",
            ])
        );
        $respResolve->assertOk();
        $this->assertCount(2, $respResolve->json('data'));
    }

    /**
     * Test 5: Global SelectOptionController endpoints
     */
    public function test_global_select_option_endpoints(): void
    {
        // Siswa endpoint
        $respSiswa = $this->actingAs($this->admin)->getJson(
            route('options.select.siswa', ['kelas_id' => $this->rombel12->id, 'page' => 1, 'per_page' => 3])
        );
        $respSiswa->assertOk();
        $this->assertCount(3, $respSiswa->json('data'));
        $this->assertSame(5, $respSiswa->json('pagination.total'));

        // Rombel endpoint
        $respRombel = $this->actingAs($this->admin)->getJson(
            route('options.select.rombel', ['jurusan_id' => $this->pplg->id])
        );
        $respRombel->assertOk();
        $this->assertNotEmpty($respRombel->json('data'));

        // Mitra PKL endpoint
        $this->createPartner('PT Global Test', 5);
        $respMitra = $this->actingAs($this->admin)->getJson(
            route('options.select.mitra', ['jurusan_id' => $this->pplg->id])
        );
        $respMitra->assertOk();
        $this->assertNotEmpty($respMitra->json('data'));
    }
}
