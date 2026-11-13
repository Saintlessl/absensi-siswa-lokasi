<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Izin extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_type',
        'siswa_id',
        'guru_id',
        'tanggal',
        'tanggal_mulai',
        'tanggal_selesai',
        'durasi_tipe',
        'jam_mulai',
        'jam_selesai',
        'jenis',
        'keterangan',
        'lampiran_path',
        'status',
        'keputusan_catatan',
        'decided_by',
        'decided_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'decided_at' => 'datetime',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function getApplicantNameAttribute(): string
    {
        if ($this->applicant_type === 'guru') {
            return $this->guru?->nama ?? 'Guru (Tidak Ditemukan)';
        }
        return $this->siswa?->nama ?? 'Siswa (Tidak Ditemukan)';
    }

    public function getApplicantIdentifierAttribute(): string
    {
        if ($this->applicant_type === 'guru') {
            return $this->guru?->nip ?? ($this->guru?->nuptk ?? '-');
        }
        return $this->siswa?->nis ?? ($this->siswa?->nisn ?? '-');
    }

    public function getApplicantDetailAttribute(): string
    {
        if ($this->applicant_type === 'guru') {
            return $this->guru?->mapel ?? 'Tenaga Pendidik';
        }
        return $this->siswa?->kelas?->nama_kelas ?? '-';
    }

    public function getJenisLabelAttribute(): string
    {
        $map = [
            'izin'                => 'Izin',
            'sakit'               => 'Sakit',
            'keperluan_keluarga'  => 'Keperluan Keluarga',
            'keperluan_pribadi'   => 'Keperluan Pribadi',
            'dinas_luar'          => 'Dinas Luar',
            'keperluan_sekolah'   => 'Keperluan Sekolah',
            'lainnya'             => 'Lainnya',
        ];

        return $map[$this->jenis] ?? ucfirst(str_replace('_', ' ', $this->jenis));
    }

    public function getDurasiLabelAttribute(): string
    {
        if ($this->durasi_tipe === 'sebagian_hari') {
            $jam = ($this->jam_mulai && $this->jam_selesai) ? " ({$this->jam_mulai} - {$this->jam_selesai})" : '';
            return 'Sebagian Hari' . $jam;
        }

        if ($this->durasi_tipe === 'rentang_hari' && $this->tanggal_mulai && $this->tanggal_selesai && $this->tanggal_mulai->ne($this->tanggal_selesai)) {
            $days = $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
            return "Rentang Hari ({$days} hari)";
        }

        return 'Sehari Penuh';
    }

    public function getLampiranUrlAttribute(): ?string
    {
        if ($this->lampiran_path && Storage::disk('public')->exists($this->lampiran_path)) {
            return asset('storage/' . $this->lampiran_path);
        }
        return null;
    }
}
