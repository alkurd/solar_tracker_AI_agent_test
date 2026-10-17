<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class TrackerSafetyState
{
    private const EMERGENCY_STOP_KEY = 'tracker.emergency_stop_active';

    public function isEmergencyStopActive(): bool
    {
        return Cache::get(self::EMERGENCY_STOP_KEY, false) === true;
    }

    public function activateEmergencyStop(): void
    {
        Cache::forever(self::EMERGENCY_STOP_KEY, true);
    }

    public function clearEmergencyStop(): void
    {
        Cache::forget(self::EMERGENCY_STOP_KEY);
    }
}