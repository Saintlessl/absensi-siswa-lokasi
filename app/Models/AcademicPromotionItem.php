<?php

namespace App\Models;

use App\Models\AcademicPromotionBatch;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicPromotionItem extends Model
{
    use HasFactory;

    protected $table = 'academic_promotion_items';

    protected $fillable = [
        'batch_id',
        'siswa_id',
        'action',
        'kelas_asal_id',
        'kelas_tujuan_id',
        'tingkat_asal',
        'tingkat_tujuan',
        'status',
        'error_message',
        'alasan',
        'catatan',
    ];

    protected $casts = [
        'batch_id'        => 'integer',
        'siswa_id'        => 'integer',
        'kelas_asal_id'   => 'integer',
        'kelas_tujuan_id' => 'integer',
        'tingkat_asal'    => 'integer',
        'tingkat_tujuan'  => 'integer',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(AcademicPromotionBatch::class, 'batch_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function kelasAsal(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_asal_id');
    }

    public function kelasTujuan(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_tujuan_id');
    }
}
