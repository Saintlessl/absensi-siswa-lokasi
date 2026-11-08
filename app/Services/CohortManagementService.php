<?php

namespace App\Services;

use App\Models\Angkatan;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CohortManagementService
{
    /**
     * Create a new Angkatan with automatic defaults for calculated fields.
     */
    public function createCohort(array $data, ?int $createdByUserId = null): Angkatan
    {
        $nomor = (int) $data['nomor_angkatan'];
        $tahunMasuk = (int) $data['tahun_masuk'];
        $jurusanId = !empty($data['jurusan_id']) ? (int) $data['jurusan_id'] : null;

        if (!$jurusanId) {
            $jurusanId = DB::table('jurusans')->value('id');
        }

        $jurusan = $jurusanId ? DB::table('jurusans')->find($jurusanId) : null;
        $jurusanKode = $jurusan ? $jurusan->kode_jurusan : '';

        $namaAngkatan = !empty($data['nama_angkatan'])
            ? trim($data['nama_angkatan'])
            : ($jurusanKode ? "Angkatan {$nomor} {$jurusanKode}" : "Angkatan {$nomor}");

        $kodeAngkatan = !empty($data['kode_angkatan'])
            ? strtoupper(trim($data['kode_angkatan']))
            : ($jurusanKode ? "AKT-{$jurusanKode}-{$nomor}" : "AKT-{$nomor}");

        $tahunAjaranMasuk = !empty($data['tahun_ajaran_masuk'])
            ? trim($data['tahun_ajaran_masuk'])
            : sprintf('%d/%d', $tahunMasuk, $tahunMasuk + 1);

        $tahunLulusRencana = !empty($data['tahun_lulus_rencana'])
            ? (int) $data['tahun_lulus_rencana']
            : ($tahunMasuk + 3);

        $tahunAjaranKelulusan = !empty($data['tahun_ajaran_kelulusan'])
            ? trim($data['tahun_ajaran_kelulusan'])
            : sprintf('%d/%d', $tahunLulusRencana - 1, $tahunLulusRencana);

        $status = $data['status'] ?? 'aktif';

        return Angkatan::create([
            'jurusan_id'             => $jurusanId,
            'nomor_angkatan'         => $nomor,
            'nama_angkatan'          => $namaAngkatan,
            'kode_angkatan'          => $kodeAngkatan,
            'tahun_masuk'            => $tahunMasuk,
            'tahun_ajaran_masuk'     => $tahunAjaranMasuk,
            'tahun_lulus_rencana'    => $tahunLulusRencana,
            'tahun_ajaran_kelulusan' => $tahunAjaranKelulusan,
            'status'                 => $status,
            'tanggal_kelulusan'      => $data['tanggal_kelulusan'] ?? null,
            'keterangan'             => $data['keterangan'] ?? null,
            'created_by'             => $createdByUserId,
        ]);
    }

    /**
     * Update an existing Angkatan.
     */
    public function updateCohort(Angkatan $angkatan, array $data): Angkatan
    {
        $nomor = isset($data['nomor_angkatan']) ? (int) $data['nomor_angkatan'] : $angkatan->nomor_angkatan;
        $tahunMasuk = isset($data['tahun_masuk']) ? (int) $data['tahun_masuk'] : $angkatan->tahun_masuk;

        $updateData = [];

        if (isset($data['nomor_angkatan'])) {
            $updateData['nomor_angkatan'] = $nomor;
        }

        if (isset($data['nama_angkatan'])) {
            $updateData['nama_angkatan'] = trim($data['nama_angkatan']);
        }

        if (isset($data['kode_angkatan'])) {
            $updateData['kode_angkatan'] = strtoupper(trim($data['kode_angkatan']));
        }

        if (isset($data['tahun_masuk'])) {
            $updateData['tahun_masuk'] = $tahunMasuk;
            if (empty($data['tahun_ajaran_masuk'])) {
                $updateData['tahun_ajaran_masuk'] = sprintf('%d/%d', $tahunMasuk, $tahunMasuk + 1);
            }
        }

        if (isset($data['tahun_ajaran_masuk'])) {
            $updateData['tahun_ajaran_masuk'] = trim($data['tahun_ajaran_masuk']);
        }

        if (isset($data['tahun_lulus_rencana'])) {
            $updateData['tahun_lulus_rencana'] = (int) $data['tahun_lulus_rencana'];
        }

        if (isset($data['tahun_ajaran_kelulusan'])) {
            $updateData['tahun_ajaran_kelulusan'] = trim($data['tahun_ajaran_kelulusan']);
        }

        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
            // If status changed to selesai and tanggal_kelulusan is empty, set default today
            if ($data['status'] === 'selesai' && empty($data['tanggal_kelulusan']) && empty($angkatan->tanggal_kelulusan)) {
                $updateData['tanggal_kelulusan'] = now()->toDateString();
            }
        }

        if (array_key_exists('tanggal_kelulusan', $data)) {
            $updateData['tanggal_kelulusan'] = $data['tanggal_kelulusan'];
        }

        if (array_key_exists('keterangan', $data)) {
            $updateData['keterangan'] = $data['keterangan'];
        }

        $angkatan->update($updateData);

        return $angkatan->fresh();
    }

    /**
     * Compute comprehensive statistics for a given cohort dynamically from database.
     */
    public function calculateStats(Angkatan $angkatan): array
    {
        $statusCounts = DB::table('siswas')
            ->where('angkatan_id', $angkatan->id)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $aktif      = (int) ($statusCounts['aktif'] ?? 0);
        $proses     = (int) ($statusCounts['dalam_proses_kelulusan'] ?? 0);
        $lulus      = (int) ($statusCounts['lulus'] ?? 0);
        $tidakLulus = (int) ($statusCounts['tidak_lulus'] ?? 0);
        $pindah     = (int) ($statusCounts['pindah'] ?? 0);
        $keluar     = (int) ($statusCounts['keluar'] ?? 0);
        $total      = array_sum($statusCounts);

        $rombelCount = DB::table('kelas')
            ->where(function ($q) use ($angkatan) {
                $q->whereIn('id', function ($sub) use ($angkatan) {
                    $sub->select('kelas_id')->from('siswas')->where('angkatan_id', $angkatan->id)->whereNotNull('kelas_id');
                })
                ->orWhereIn('id', function ($sub) use ($angkatan) {
                    $sub->select('kelas_id')->from('student_enrollments')
                        ->whereIn('siswa_id', function ($sub2) use ($angkatan) {
                            $sub2->select('id')->from('siswas')->where('angkatan_id', $angkatan->id);
                        });
                });
            })
            ->count();

        return [
            'angkatan_id'            => $angkatan->id,
            'nomor_angkatan'         => $angkatan->nomor_angkatan,
            'nama_angkatan'          => $angkatan->nama_angkatan,
            'kode_angkatan'          => $angkatan->kode_angkatan,
            'tahun_masuk'            => $angkatan->tahun_masuk,
            'tahun_ajaran_masuk'     => $angkatan->tahun_ajaran_masuk,
            'tahun_lulus'            => $angkatan->tahun_lulus_rencana,
            'tahun_ajaran_kelulusan' => $angkatan->tahun_ajaran_kelulusan,
            'status'                 => $angkatan->status,
            'status_label'           => $angkatan->status_label,
            'total_siswa'            => $total,
            'siswa_aktif'            => $aktif,
            'siswa_proses'           => $proses,
            'siswa_lulus'            => $lulus,
            'siswa_tidak_lulus'      => $tidakLulus,
            'siswa_pindah'           => $pindah,
            'siswa_keluar'           => $keluar,
            'rombel_count'           => max($rombelCount, 1),
        ];
    }

    /**
     * Compute statistics for an angkatan scoped to a specific jurusan.
     */
    public function calculateStatsForMajor(Angkatan $angkatan, int $jurusanId): array
    {
        $studentIds = DB::table('siswas')
            ->where('angkatan_id', $angkatan->id)
            ->where(function ($q) use ($jurusanId) {
                $q->whereIn('kelas_id', function ($sub) use ($jurusanId) {
                    $sub->select('id')->from('kelas')->where('jurusan_id', $jurusanId);
                })
                ->orWhereIn('id', function ($sub) use ($jurusanId) {
                    $sub->select('siswa_id')->from('student_enrollments')
                        ->whereIn('kelas_id', function ($sub2) use ($jurusanId) {
                            $sub2->select('id')->from('kelas')->where('jurusan_id', $jurusanId);
                        });
                });
            })
            ->pluck('id');

        $statusCounts = DB::table('siswas')
            ->whereIn('id', $studentIds)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $lulus      = (int) ($statusCounts['lulus'] ?? 0);
        $aktif      = (int) ($statusCounts['aktif'] ?? 0);
        $proses     = (int) ($statusCounts['dalam_proses_kelulusan'] ?? 0);
        $tidakLulus = (int) ($statusCounts['tidak_lulus'] ?? 0);
        $pindah     = (int) ($statusCounts['pindah'] ?? 0);
        $keluar     = (int) ($statusCounts['keluar'] ?? 0);
        $total      = count($studentIds);

        $rombelCount = DB::table('kelas')
            ->where('jurusan_id', $jurusanId)
            ->where(function ($q) use ($studentIds) {
                $q->whereIn('id', function ($sub) use ($studentIds) {
                    $sub->select('kelas_id')->from('siswas')->whereIn('id', $studentIds);
                })
                ->orWhereIn('id', function ($sub) use ($studentIds) {
                    $sub->select('kelas_id')->from('student_enrollments')->whereIn('siswa_id', $studentIds);
                });
            })
            ->count();

        return [
            'angkatan_id'            => $angkatan->id,
            'jurusan_id'             => $jurusanId,
            'nomor_angkatan'         => $angkatan->nomor_angkatan,
            'nama_angkatan'          => $angkatan->nama_angkatan,
            'kode_angkatan'          => $angkatan->kode_angkatan,
            'tahun_masuk'            => $angkatan->tahun_masuk,
            'tahun_ajaran_masuk'     => $angkatan->tahun_ajaran_masuk,
            'tahun_lulus'            => $angkatan->tahun_lulus_rencana,
            'tahun_ajaran_kelulusan' => $angkatan->tahun_ajaran_kelulusan,
            'status'                 => $angkatan->status,
            'status_label'           => $angkatan->status_label,
            'total_siswa'            => $total,
            'siswa_lulus'            => $lulus,
            'siswa_aktif'            => $aktif,
            'siswa_proses'           => $proses,
            'siswa_tidak_lulus'      => $tidakLulus,
            'siswa_pindah'           => $pindah,
            'siswa_keluar'           => $keluar,
            'rombel_count'           => max($rombelCount, 1),
        ];
    }

    /**
     * Find or auto-determine the cohort based on the student's entry year and major.
     */
    public function determineCohortByYear(int $entryYear, ?int $jurusanId = null): ?Angkatan
    {
        if ($jurusanId) {
            $matched = Angkatan::where('jurusan_id', $jurusanId)->where('tahun_masuk', $entryYear)->first();
            if ($matched) {
                return $matched;
            }
            return Angkatan::where('jurusan_id', $jurusanId)->where('status', 'aktif')->orderByDesc('tahun_masuk')->first();
        }

        // Direct match by tahun_masuk
        $matched = Angkatan::where('tahun_masuk', $entryYear)->first();
        if ($matched) {
            return $matched;
        }

        // Nearest or fallback: find active cohort
        return Angkatan::where('status', 'aktif')->orderByDesc('tahun_masuk')->first();
    }

    /**
     * Safely delete an angkatan, guarding against deleting cohorts with linked students.
     *
     * @throws ValidationException
     */
    public function deleteCohort(Angkatan $angkatan): bool
    {
        $studentCount = $angkatan->siswas()->count();

        if ($studentCount > 0) {
            throw ValidationException::withMessages([
                'angkatan' => "Tidak dapat menghapus {$angkatan->nama_angkatan} karena masih memiliki {$studentCount} siswa terdaftar. Pindahkan atau ubah data siswa terlebih dahulu.",
            ]);
        }

        return (bool) $angkatan->delete();
    }
}
