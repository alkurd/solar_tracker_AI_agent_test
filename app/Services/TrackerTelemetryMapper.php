<?php

namespace App\Services;

use Illuminate\Support\Arr;

class TrackerTelemetryMapper
{
    public function map(array $payload): array
    {
        $mapped = [];

        foreach (config('tracker.telemetry.field_map', []) as $field => $sourcePath) {
            if (! is_string($sourcePath) || ! Arr::has($payload, $sourcePath)) {
                continue;
            }

            $value = data_get($payload, $sourcePath);
            $valueMap = config("tracker.telemetry.value_map.{$field}", []);

            if (is_scalar($value) && is_array($valueMap)) {
                $value = $valueMap[(string) $value] ?? $value;
            }

            $mapped[$field] = $value;
        }

        return $mapped;
    }
}