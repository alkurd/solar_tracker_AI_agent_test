<?php

namespace App\Http\Controllers;

use App\Models\TrackerLog;
use App\Services\TrackerSafetyState;
use App\Services\TrackerTelemetryMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrackerDataController extends Controller
{
    public function __construct(
        private readonly TrackerSafetyState $safetyState,
        private readonly TrackerTelemetryMapper $telemetryMapper,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $token = config('services.tracker.ingest_token');
        $providedToken = $request->bearerToken();

        abort_unless(
            is_string($token) && $token !== '' && is_string($providedToken) && hash_equals($token, $providedToken),
            401,
            'A valid telemetry bearer token is required.'
        );

        $validated = Validator::make($this->telemetryMapper->map($request->all()), [
            'datum_tijd' => ['sometimes', 'date'],
            'positie_actuator_1' => ['required', 'numeric'],
            'positie_actuator_2' => ['required', 'numeric'],
            'opbrengst' => ['required', 'numeric'],
            'status' => ['required', 'string', 'max:255'],
            'modus' => ['sometimes', 'nullable', 'string', 'in:automatic,manual'],
            'systeem_actief' => ['sometimes', 'nullable', 'boolean'],
            'noodstop_actief' => ['sometimes', 'nullable', 'boolean'],
            'verbruik_actuator_1' => ['required', 'numeric'],
            'verbruik_actuator_2' => ['required', 'numeric'],
            'motor_verbruik' => ['required', 'numeric'],
            'netto_opbrengst' => ['required', 'numeric'],
            'windsnelheid' => ['required', 'numeric', 'min:0'],
        ])->validate();

        $log = TrackerLog::query()->create($validated);

        if (array_key_exists('noodstop_actief', $validated) && $validated['noodstop_actief'] !== null) {
            if (filter_var($validated['noodstop_actief'], FILTER_VALIDATE_BOOLEAN)) {
                $this->safetyState->activateEmergencyStop();
            } else {
                $this->safetyState->clearEmergencyStop();
            }
        }

        return response()->json(['data' => $log], 201);
    }
}