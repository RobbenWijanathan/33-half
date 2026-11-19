<?php

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('user:admin {email?}', function () {
    $email = strtolower($this->argument('email') ?: $this->ask('Admin email'));
    $validation = Validator::make(['email' => $email], ['email' => ['required', 'email', 'max:254']]);
    if ($validation->fails()) {
        $this->error($validation->errors()->first());

        return 1;
    }
    $user = User::where('email', $email)->first();
    if ($user) {
        if (! $this->confirm('Promote and activate '.$email.' as an admin?')) {
            return 1;
        }
    } else {
        $name = $this->ask('Admin name');
        $password = $this->secret('Password (8+ characters, letters and numbers)');
        $confirmation = $this->secret('Confirm password');
        $validation = Validator::make(['name' => $name, 'password' => $password, 'password_confirmation' => $confirmation], ['name' => ['required', 'string', 'max:120'], 'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()]]);
        if ($validation->fails()) {
            $this->error($validation->errors()->first());

            return 1;
        }
        $user = new User(['name' => $name, 'email' => $email, 'password' => $password]);
    }
    DB::transaction(function () use ($user) {
        $user->role = 'admin';
        $user->is_active = true;
        $user->save();
        AdminAuditLog::record('user.promoted', $user, 'Admin created or promoted through the local console.');
    });
    $this->info('Admin account is ready: '.$email);
})->purpose('Securely create or promote an admin account (requires server access)');
