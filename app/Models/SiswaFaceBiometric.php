<?php

namespace App\Models;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SiswaFaceBiometric extends Model
{
    use HasFactory;

    protected $table = 'siswa_face_biometrics';

    protected $fillable = [
        'siswa_id',
        'face_descriptor',
        'quality_score',
        'sample_photo',
        'registered_at',
        'is_active',
        'registered_by',
        'enrollment_status',
        'model_version',
        'status_reason',
        'enrolled_at',
        'last_verified_at'
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'enrolled_at' => 'datetime',
        'last_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'quality_score' => 'float',
    ];

    /**
     * Mutator to encrypt face descriptor vector for privacy by design
     */
    public function setFaceDescriptorAttribute($value)
    {
        if (is_array($value)) {
            $value = json_encode(array_values($value));
        }

        $this->attributes['face_descriptor'] = Crypt::encryptString($value);
    }

    /**
     * Accessor to decrypt face descriptor vector
     *
     * @return array
     */
    public function getFaceDescriptorAttribute($value)
    {
        if (!$value) {
            return [];
        }

        try {
            $decrypted = Crypt::decryptString($value);
            return json_decode($decrypted, true) ?: [];
        } catch (\Exception $e) {
            // Fallback if raw JSON exists before encryption
            return json_decode($value, true) ?: [];
        }
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function registeredByUser()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
