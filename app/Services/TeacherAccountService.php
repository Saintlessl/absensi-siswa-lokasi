<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\TeacherAccountActivation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TeacherAccountService
{
    /**
     * Create a teacher profile and integrated authentication account.
     */
    public function createTeacher(array $data, ?string $password = null, bool $requiresActivation = false): array
    {
        return DB::transaction(function () use ($data, $password, $requiresActivation) {
            $status = $requiresActivation || empty($password) ? 'pending' : ($data['status'] ?? 'active');

            // 1. Create User
            $user = User::create([
                'name'     => $data['nama'],
                'email'    => $data['email'],
                'password' => !empty($password) ? Hash::make($password) : Hash::make(Str::random(32)),
                'role'     => 'guru',
                'status'   => $status,
            ]);

            // 2. Handle Photo Upload
            $photoPath = null;
            if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
                $photoPath = $data['photo']->store('guru-photos', 'public');
            }

            // 3. Create Guru Profile
            $guru = Guru::create([
                'user_id'            => $user->id,
                'nip'                => !empty($data['nip']) ? $data['nip'] : null,
                'nuptk'              => !empty($data['nuptk']) ? $data['nuptk'] : null,
                'nama'               => $data['nama'],
                'mapel'              => $data['mapel'] ?? null,
                'jenis_kelamin'      => $data['jenis_kelamin'] ?? 'L',
                'telepon'            => $data['telepon'] ?? null,
                'alamat'             => $data['alamat'] ?? null,
                'photo_path'         => $photoPath,
                'status_kepegawaian' => $data['status_kepegawaian'] ?? 'GTT/Honorer',
                'is_active'          => isset($data['is_active']) ? (bool) $data['is_active'] : true,
                'catatan'            => $data['catatan'] ?? null,
            ]);

            // 4. Generate Activation Token if needed
            $activationUrl = null;
            if ($requiresActivation || empty($password)) {
                $activationUrl = $this->generateActivationLink($user);
            }

            return [
                'guru'           => $guru,
                'user'           => $user,
                'activation_url' => $activationUrl,
            ];
        });
    }

    /**
     * Update an existing teacher profile and user record.
     */
    public function updateTeacher(Guru $guru, array $data): Guru
    {
        return DB::transaction(function () use ($guru, $data) {
            $user = $guru->user;

            $userUpdateData = [
                'name'  => $data['nama'],
                'email' => $data['email'],
            ];

            if (!empty($data['status'])) {
                $userUpdateData['status'] = $data['status'];
            }

            if (!empty($data['password'])) {
                $userUpdateData['password'] = Hash::make($data['password']);
                if ($user->status === 'pending') {
                    $userUpdateData['status'] = 'active';
                }
            }

            $user->update($userUpdateData);

            // Handle Photo Replacement or Removal
            $photoPath = $guru->photo_path;
            if (!empty($data['remove_photo']) && $data['remove_photo'] == '1') {
                if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                    Storage::disk('public')->delete($photoPath);
                }
                $photoPath = null;
            } elseif (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
                if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                    Storage::disk('public')->delete($photoPath);
                }
                $photoPath = $data['photo']->store('guru-photos', 'public');
            }

            $guru->update([
                'nip'                => !empty($data['nip']) ? $data['nip'] : null,
                'nuptk'              => !empty($data['nuptk']) ? $data['nuptk'] : null,
                'nama'               => $data['nama'],
                'mapel'              => $data['mapel'] ?? $guru->mapel,
                'jenis_kelamin'      => $data['jenis_kelamin'] ?? $guru->jenis_kelamin,
                'telepon'            => $data['telepon'] ?? null,
                'alamat'             => $data['alamat'] ?? null,
                'photo_path'         => $photoPath,
                'status_kepegawaian' => $data['status_kepegawaian'] ?? $guru->status_kepegawaian,
                'is_active'          => isset($data['is_active']) ? (bool) $data['is_active'] : $guru->is_active,
                'catatan'            => $data['catatan'] ?? $guru->catatan,
            ]);

            return $guru->fresh(['user']);
        });
    }

    /**
     * Generate an activation link for a teacher user.
     */
    public function generateActivationLink(User $user, int $hoursValid = 72): string
    {
        // Invalidate previous unused activations
        TeacherAccountActivation::where('user_id', $user->id)
            ->whereNull('activated_at')
            ->delete();

        $rawToken = Str::random(48);
        $tokenHash = hash('sha256', $rawToken);

        TeacherAccountActivation::create([
            'user_id'    => $user->id,
            'token_hash' => $tokenHash,
            'expires_at' => Carbon::now()->addHours($hoursValid),
        ]);

        return route('teacher.activate.show', ['token' => $rawToken]);
    }

    /**
     * Find a valid activation record from raw token.
     */
    public function getValidActivation(string $rawToken): ?TeacherAccountActivation
    {
        $tokenHash = hash('sha256', $rawToken);

        $activation = TeacherAccountActivation::with('user.guru')
            ->where('token_hash', $tokenHash)
            ->whereNull('activated_at')
            ->where('expires_at', '>', Carbon::now())
            ->first();

        return $activation;
    }

    /**
     * Complete teacher account activation with their chosen password.
     */
    public function activateAccount(string $rawToken, string $newPassword): User
    {
        $activation = $this->getValidActivation($rawToken);

        if (!$activation) {
            throw ValidationException::withMessages([
                'token' => ['Tautan aktivasi tidak valid, telah kedaluwarsa, atau sudah pernah digunakan.'],
            ]);
        }

        return DB::transaction(function () use ($activation, $newPassword) {
            $user = $activation->user;

            $user->update([
                'password' => Hash::make($newPassword),
                'status'   => 'active',
            ]);

            $activation->update([
                'activated_at' => Carbon::now(),
            ]);

            return $user;
        });
    }

    /**
     * Update account status (e.g. active, suspended, deactivated).
     */
    public function setAccountStatus(User $user, string $status): void
    {
        if (!in_array($status, ['active', 'pending', 'suspended', 'deactivated'])) {
            throw new \InvalidArgumentException("Status akun {$status} tidak valid.");
        }

        $user->update(['status' => $status]);
    }
}
