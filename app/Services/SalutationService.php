<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\User;

class SalutationService
{
    /**
     * Resolve effective gender ('L', 'P', or null) from user and linked profiles.
     * Prioritizes profile tables (guru, siswa, orang_tua) according to the user's role.
     */
    public function resolveGender(User $user): ?string
    {
        $gender = null;

        if ($user->role === 'guru' || $user->role === 'kaprog') {
            $gender = $user->guru?->jenis_kelamin ?? $user->jenis_kelamin;
        } elseif ($user->role === 'siswa') {
            $gender = $user->siswa?->jenis_kelamin ?? $user->jenis_kelamin;
        } elseif ($user->role === 'orangtua') {
            $gender = $user->orangTua?->jenis_kelamin ?? $user->jenis_kelamin;
        } else {
            // For admin, superadmin, or general staff
            $gender = $user->jenis_kelamin ?? $user->guru?->jenis_kelamin;
        }

        if ($gender === 'L' || $gender === 'P') {
            return $gender;
        }

        return null;
    }

    /**
     * Get appropriate honorific based on role and gender.
     * - Guru / Kaprog: Pak / Bu
     * - Siswa: Empty string (direct name)
     * - Orang Tua: Bapak / Ibu
     * - Admin / Superadmin: Pak / Bu
     * - Fallback / Unknown gender: Empty string
     */
    public function getHonorific(User $user): string
    {
        $gender = $this->resolveGender($user);

        if (!$gender) {
            return '';
        }

        // Students must always be addressed directly by name without Pak/Bu
        if ($user->role === 'siswa') {
            return '';
        }

        if ($user->role === 'orangtua') {
            return $gender === 'L' ? 'Bapak' : 'Ibu';
        }

        // Guru, Kaprog, Superadmin, Admin, and other staff
        return $gender === 'L' ? 'Pak' : 'Bu';
    }

    /**
     * Resolve the cleanest display name for the user.
     */
    public function resolveName(User $user): string
    {
        if (($user->role === 'guru' || $user->role === 'kaprog') && $user->guru && !empty($user->guru->nama)) {
            return $user->guru->nama;
        }

        if ($user->role === 'siswa' && $user->siswa && !empty($user->siswa->nama)) {
            return $user->siswa->nama;
        }

        if ($user->role === 'orangtua' && $user->orangTua && !empty($user->orangTua->nama)) {
            return $user->orangTua->nama;
        }

        return $user->name ?: 'Pengguna';
    }

    /**
     * Get the full personalized salutation name (e.g. "Pak Budi", "Bu Rina", "Ahmad Fauzi").
     * Intelligently avoids redundant prefixes if the user's name already starts with an honorific.
     */
    public function getSalutationName(User $user): string
    {
        $honorific = $this->getHonorific($user);
        $name = $this->resolveName($user);

        if (!empty($honorific)) {
            if (preg_match('/^(' . preg_quote($honorific, '/') . ')\b/i', $name)) {
                return $name;
            }
            if ($honorific === 'Pak' && preg_match('/^(bapak)\b/i', $name)) {
                return preg_replace('/^(bapak)\b/i', 'Pak', $name);
            }
            if ($honorific === 'Bu' && preg_match('/^(ibu)\b/i', $name)) {
                return preg_replace('/^(ibu)\b/i', 'Bu', $name);
            }
            if ($honorific === 'Bapak' && preg_match('/^(pak)\b/i', $name)) {
                return preg_replace('/^(pak)\b/i', 'Bapak', $name);
            }
            if ($honorific === 'Ibu' && preg_match('/^(bu)\b/i', $name)) {
                return preg_replace('/^(bu)\b/i', 'Ibu', $name);
            }

            return "{$honorific} {$name}";
        }

        return $name;
    }

    /**
     * Generate dynamic welcome greeting for dashboard banners and login messages.
     * Examples:
     * - "Selamat datang kembali, Pak Budi."
     * - "Selamat datang kembali, Bu Rina."
     * - "Selamat datang kembali, Ahmad."
     */
    public function getWelcomeGreeting(User $user, bool $isReturning = true): string
    {
        $prefix = $isReturning ? 'Selamat datang kembali' : 'Selamat datang';
        $salutationName = $this->getSalutationName($user);

        return "{$prefix}, {$salutationName}!";
    }

    /**
     * Human-readable gender label.
     */
    public function getGenderLabel(?string $gender): string
    {
        return match ($gender) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => 'Belum diatur',
        };
    }

    /**
     * Helper for Guru model directly.
     */
    public function forGuru(Guru $guru): string
    {
        $name = $guru->nama;
        if ($guru->jenis_kelamin === 'L') {
            if (preg_match('/^(pak|bapak)\b/i', $name)) {
                return preg_replace('/^(bapak)\b/i', 'Pak', $name);
            }
            return 'Pak ' . $name;
        }
        if ($guru->jenis_kelamin === 'P') {
            if (preg_match('/^(bu|ibu)\b/i', $name)) {
                return preg_replace('/^(ibu)\b/i', 'Bu', $name);
            }
            return 'Bu ' . $name;
        }
        return $name;
    }

    /**
     * Helper for OrangTua model directly.
     */
    public function forOrangTua(OrangTua $orangTua): string
    {
        $name = $orangTua->nama;
        if ($orangTua->jenis_kelamin === 'L') {
            if (preg_match('/^(bapak|pak)\b/i', $name)) {
                return preg_replace('/^(pak)\b/i', 'Bapak', $name);
            }
            return 'Bapak ' . $name;
        }
        if ($orangTua->jenis_kelamin === 'P') {
            if (preg_match('/^(ibu|bu)\b/i', $name)) {
                return preg_replace('/^(bu)\b/i', 'Ibu', $name);
            }
            return 'Ibu ' . $name;
        }
        return $name;
    }

    /**
     * Helper for Siswa model directly (always direct name).
     */
    public function forSiswa(Siswa $siswa): string
    {
        return $siswa->nama;
    }
}
