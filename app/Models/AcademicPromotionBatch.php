<?php

namespace App\Models;

use App\Models\AcademicPromotionItem;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicPromotionBatch extends Model
{
    use HasFactory;

    protected $table = 'academic_promotion_batches';

    protected $fillable = [
        'batch_code',
        'tahun_ajaran_asal_id',
        'tahun_ajaran_tujuan_id',
        'total_siswa',
        'total_naik',
        'total_tinggal',
        'total_lulus',
        'total_pindah',
        'total_keluar',
        'status',
        'summary_meta',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'summary_meta'  => 'array',
        'total_siswa'   => 'integer',
        'total_naik'    => 'integer',
        'total_tinggal' => 'integer',
        'total_lulus'   => 'integer',
        'total_pindah'  => 'integer',
        'total_keluar'  => 'integer',
        'tahun_ajaran_asal_id'   => 'integer',
        'tahun_ajaran_tujuan_id' => 'integer',
    ];

    public function tahunAjaranAsal(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_asal_id');
    }

    public function tahunAjaranTujuan(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_tujuan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AcademicPromotionItem::class, 'batch_id');
    }
}
