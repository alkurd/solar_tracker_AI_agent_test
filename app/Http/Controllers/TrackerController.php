<?php

namespace App\Http\Controllers;

use App\Models\TrackerLog;
use App\Services\OperatorActivity;
use App\Services\TrackerSafetyState;
use App\Services\TrackerPlcClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class TrackerController extends Controller
{
    public function __construct(
        private readonly TrackerPlcClient $plcClient,
        private readonly OperatorActivity $operatorActivity,
        private readonly TrackerSafetyState $safetyState,
    ) {}

    public function index(): View
    {
        return view('tracker.dashboard', $this->dashboardData());
    }

    public function control(): View
    {
        return view('tracker.controls', $this->dashboardData());
    }

    private function dashboardData(): array
    {
        $latestLog = TrackerLog::query()->latest('datum_tijd')->first();
        $reportedMode = strtolower((string) ($latestLog?->modus ?? $latestLog?->status));
        $mode = match ($reportedMode) {
            'auto', 'automatic' => 'automatic',
            'manual' => 'manual',
            default => null,
        };

        return [
            'latestLog' => $latestLog,
            'yieldData' => $this->yieldData(),
            'operatingMode' => $mode,
            'systemActive' => $latestLog?->systeem_actief,
            'emergencyStopActive' => $this->safetyState->isEmergencyStopActive() || $latestLog?->noodstop_actief === true,
        ];
    }

    private function yieldData(): \Illuminate\Support\Collection
    {
        return TrackerLog::query()
            ->where('datum_tijd', '>=', now()->subDays(14))
            ->orderBy('datum_tijd')
            ->get(['datum_tijd', 'opbrengst'])
            ->map(fn (TrackerLog $sample): array => [
                'x' => $sample->datum_tijd->valueOf(),
                'y' => $sample->opbrengst,
            ])
            ->values();
    }

    public function oost(Request $request): JsonResponse|RedirectResponse
    {
        return $this->sendCommand($request, 'oost');
    }

    public function west(Request $request): JsonResponse|RedirectResponse
    {
        return $this->sendCommand($request, 'west');
    }

    public function poot_omhoog(Request $request): JsonResponse|RedirectResponse
    {
        return $this->sendCommand($request, 'poot_omhoog');
    }

    public function poot_omlaag(Request $request): JsonResponse|RedirectResponse
    {
        return $this->sendCommand($request, 'poot_omlaag');
    }

    public function setMode(Request $request, string $mode): JsonResponse|RedirectResponse
    {
        abort_unless(in_array($mode, ['automatic', 'manual'], true), 404);

        return $this->sendCommand($request, 'set_mode', ['mode' => $mode]);
    }

    public function noodstop(Request $request): JsonResponse|RedirectResponse
    {
        return $this->sendCommand($request, 'noodstop');
    }

    public function resetNoodstop(Request $request): JsonResponse|RedirectResponse
    {
        return $this->sendCommand($request, 'noodstop_reset');
    }

    private function sendCommand(Request $request, string $command, array $parameters = []): JsonResponse|RedirectResponse
    {
        $this->operatorActivity->mark();

        try {
            $response = $this->plcClient->send($command, $parameters);
        } catch (RuntimeException $exception) {
            return $this->commandError($request, $exception->getMessage(), 422);
        }

        if (! $response->successful()) {
            return $this->commandError($request, 'The PLC API rejected the command (HTTP '.$response->status().').', 502);
        }

        if ($command === 'noodstop') {
            $this->safetyState->activateEmergencyStop();
        } elseif ($command === 'noodstop_reset') {
            $this->safetyState->clearEmergencyStop();
        }

        $message = match ($command) {
            'set_mode' => 'PLC accepted the '.$parameters['mode'].' mode request. Waiting for telemetry confirmation.',
            'noodstop' => 'PLC acknowledged the emergency-stop command.',
            'noodstop_reset' => 'PLC acknowledged the emergency-stop reset.',
            default => 'PLC accepted command: '.$command,
        };

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'emergencyStopActive' => $this->safetyState->isEmergencyStopActive(),
            ]);
        }

        return back()->with('status', $message);
    }

    private function commandError(Request $request, string $message, int $status): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->withErrors(['plc' => $message]);
    }
}