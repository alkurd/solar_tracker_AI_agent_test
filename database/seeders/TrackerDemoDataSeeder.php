<?php

namespace Database\Seeders;

use App\Models\TrackerLog;
use Illuminate\Database\Seeder;

class TrackerDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $latestSample = now()->startOfHour()->addMinutes(now()->minute >= 30 ? 30 : 0);

        for ($index = 671; $index >= 0; $index--) {
            $sampleTime = $latestSample->copy()->subMinutes($index * 30);
            $hourOfDay = (int) $sampleTime->format('G') + ((int) $sampleTime->format('i') / 60);
            $daylight = max(0, sin((($hourOfDay - 6) / 12) * M_PI));
            $weatherFactor = 0.82 + ((sin($index * 0.19) + 1) * 0.09);
            $production = round($daylight * 8.5 * $weatherFactor, 3);
            $actuatorPosition = round(42 + ($daylight * 13), 2);

            TrackerLog::query()->updateOrCreate(
                ['datum_tijd' => $sampleTime],
                [
                    'positie_actuator_1' => $actuatorPosition,
                    'positie_actuator_2' => round(44 + ($daylight * 9), 2),
                    'opbrengst' => $production,
                    'status' => 'demo',
                    'modus' => 'automatic',
                    'systeem_actief' => true,
                    'noodstop_actief' => false,
                    'verbruik_actuator_1' => round(0.15 + ($daylight * 0.18), 3),
                    'verbruik_actuator_2' => round(0.12 + ($daylight * 0.16), 3),
                    'motor_verbruik' => round(0.2 + ($daylight * 0.25), 3),
                    'netto_opbrengst' => max(0, round($production - 0.3, 3)),
                    'windsnelheid' => round(2.4 + (abs(sin($index * 0.11)) * 5.2), 2),
                ]
            );
        }
    }
}