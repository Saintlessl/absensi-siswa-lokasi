<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'file_path',
        'import_type',
        'total_rows',
        'successful_rows',
        'failed_rows',
        'skipped_rows',
        'status',
        'error_details',
        'imported_by',
    ];

    protected $casts = [
        'total_rows'      => 'integer',
        'successful_rows' => 'integer',
        'failed_rows'     => 'integer',
        'skipped_rows'    => 'integer',
        'error_details'   => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
