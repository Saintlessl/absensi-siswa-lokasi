<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingJournalAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'teaching_journal_id',
        'siswa_id',
        'status',
        'catatan',
    ];

    public function teachingJournal(): BelongsTo
    {
        return $this->belongsTo(TeachingJournal::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }
}
