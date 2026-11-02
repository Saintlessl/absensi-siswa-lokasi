<?php

namespace App\Services;

use App\Models\ParentAccountActivation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ParentAccountService
{
    public function createPendingAccount(string $name, string $phone): User
    {
        // This internal address is never exposed as a credential or derived password.
        // The parent sets their own password through the one-time activation flow.
        return User::create([
            'name' => $name,
            'email' => 'wali.' . preg_replace('/\D/', '', $phone) . '.' . Str::lower(Str::random(8)) . '@myskanic.com',
            'password' => Hash::make(Str::random(64)),
            'role' => 'orangtua',
        ]);
    }

    public function issueActivation(User $user): string
    {
        $token = Str::random(64);
        ParentAccountActivation::updateOrCreate(
            ['user_id' => $user->id],
            ['token_hash' => hash('sha256', $token), 'expires_at' => now()->addDays(7), 'activated_at' => null]
        );

        return route('parent.activate.show', ['token' => $token]);
    }
}
