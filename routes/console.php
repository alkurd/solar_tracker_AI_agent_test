<?php

use Illuminate\Foundation\Inspiring;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use App\Services\OperatorActivity;
use App\Services\TrackerSafetyState;
use App\Services\TrackerPlcClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tracker:create-operator {email?} {--name=}', function (?string $email = null): int {
    $name = trim((string) ($this->option('name') ?: $this->ask('Operator name')));
    $email = trim((string) ($email ?: $this->ask('Operator email')));

    if ($name === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $this->error('A name and valid email address are required.');

        return 1;
    }

    if (User::query()->where('email', $email)->exists()) {
        $this->error('An account with that email address already exists.');

        return 1;
    }

    $password = $this->secret('Password (at least 12 characters)');
    $confirmation = $this->secret('Confirm password');

    if (! is_string($password) || strlen($password) < 12 || $password !== $confirmation) {
        $this->error('The passwords must match and contain at least 12 characters.');

        return 1;
    }

    User::query()->create([
        'name' => $name,
        'email' => $email,
        'password' => $password,
    ]);

    $this->info('Operator account created.');

    return 0;
})->purpose('Create an authenticated solar tracker operator account');

Artisan::command('tracker:reset-operator-password {email}', function (string $email): int {
    $user = User::query()->where('email', $email)->first();

    if (! $user) {
        $this->error('No operator account exists with that email address.');

        return 1;
    }

    $password = $this->secret('New password (at least 12 characters)');
    $confirmation = $this->secret('Confirm new password');

    if (! is_string($password) || strlen($password) < 12 || $password !== $confirmation) {
        $this->error('The passwords must match and contain at least 12 characters.');

        return 1;
    }

    $user->password = $password;
    $user->save();

    $this->info('Operator password updated.');

    return 0;
})->purpose('Reset an operator password without exposing it in the command line');

Artisan::command('tracker:check-idle-mode', function (): int {
    $lastActivity = Cache::get(OperatorActivity::LAST_ACTIVITY_KEY);

    if (! is_int($lastActivity) || now()->timestamp - $lastActivity <= 600) {
        return 0;
    }

    if (Cache::get(OperatorActivity::AUTO_MODE_APPLIED_KEY) === $lastActivity) {
        return 0;
    }

    if (app(TrackerSafetyState::class)->isEmergencyStopActive()) {
        return 0;
    }

    try {
        $response = app(TrackerPlcClient::class)->send('set_mode', ['mode' => 'automatic']);
    } catch (RuntimeException $exception) {
        $this->error($exception->getMessage());

        return 1;
    }

    if (! $response->successful()) {
        $this->error('The PLC API rejected the automatic-mode request (HTTP '.$response->status().').');

        return 1;
    }

    Cache::put(OperatorActivity::AUTO_MODE_APPLIED_KEY, $lastActivity, now()->addDay());
    $this->info('PLC automatic mode requested after operator inactivity.');

    return 0;
})->purpose('Set the PLC to automatic mode after ten minutes without operator activity');

Schedule::command('tracker:check-idle-mode')->everyMinute()->withoutOverlapping();
