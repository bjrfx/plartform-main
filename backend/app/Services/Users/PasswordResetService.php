<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    public function createEntry(User $user): string
    {
        $token = Str::uuid();
        DB::table('password_reset_tokens')->insert([
            'user_id' => $user->getKey(),
            'token' => $token,
            'created_at' => now(),
        ]);

        return $token;
    }

    /**
     * @throws ValidationException
     */
    public function retrieveEntry(string $token): string
    {
        $record = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->where('created_at', '<', now()->subHours(72))
            ->first();

        if (is_null($record)) {
            throw ValidationException::withMessages(['message' => 'Invalid or expired token.']);
        }

        $userId = $record->user_id;
        // Delete the token after activation
        $record->delete($token);

        return $userId;
    }
}
