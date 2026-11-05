<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StudentPortfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'jurusan_id',
        'judul',
        'slug',
        'kategori',
        'deskripsi',
        'cover_image_path',
        'attachment_path',
        'demo_url',
        'repository_url',
        'is_tefa_product',
        'status',
        'moderation_notes',
        'approved_by',
        'approved_at',
        'views_count',
    ];

    protected $casts = [
        'is_tefa_product' => 'boolean',
        'approved_at'     => 'datetime',
        'views_count'     => 'integer',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }
}
