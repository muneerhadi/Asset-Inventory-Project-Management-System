<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Reset a user's password. Use when a user has "This password does not use the Bcrypt algorithm."
 * Run on server: php artisan user:reset-password "user@example.com" "NewSecurePassword123" 
 */
Artisan::command('user:reset-password {email : The user email} {password : The new password}', function () {
    $email = $this->argument('email');
    $newPassword = $this->argument('password'); 

    $updated = DB::table('users')->where('email', $email)->update([ 
        'password' => Hash::make($newPassword),
    ]);

    if ($updated) {
        $this->info("Password reset for: {$email}");
    } else {
        $this->error("User not found: {$email}");
        return 1;
    }
})->purpose('Reset a user password (fixes non-Bcrypt / corrupted hash errors)');

/**
 * List users whose stored password is not a valid Bcrypt hash.
 * These users will get "This password does not use the Bcrypt algorithm" on login.
 */
Artisan::command('user:list-invalid-passwords', function () {
    $users = DB::table('users')->get(['id', 'name', 'email', 'password']);
    $invalid = [];

    foreach ($users as $u) {
        $p = $u->password ?? '';
        $valid = strlen($p) === 60 && (str_starts_with($p, '$2y$') || str_starts_with($p, '$2a$'));
        if (! $valid) {
            $invalid[] = ['id' => $u->id, 'name' => $u->name, 'email' => $u->email];
        }
    }

    if (empty($invalid)) {
        $this->info('All users have valid Bcrypt password hashes.');
        return;
    }

    $this->warn('Users with invalid (non-Bcrypt) password hashes. Reset with: php artisan user:reset-password "<email>" "<new-password>"');
    $this->table(['id', 'name', 'email'], $invalid);
})->purpose('List users whose password is not Bcrypt (causes login 500)');
