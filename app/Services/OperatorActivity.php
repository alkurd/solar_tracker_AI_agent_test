<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class OperatorActivity
{
    public const LAST_ACTIVITY_KEY = 'tracker.operator.last_activity_at';

    public const AUTO_MODE_APPLIED_KEY = 'tracker.operator.auto_mode_applied_at';

    public function mark(): int
    {
        $timestamp = now()->timestamp;

        Cache::put(self::LAST_ACTIVITY_KEY, $timestamp, now()->addDay());
        Cache::forget(self::AUTO_MODE_APPLIED_KEY);

        return $timestamp;
    }
}