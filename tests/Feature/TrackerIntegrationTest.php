<?php

namespace Tests\Feature;

use App\Models\TrackerLog;
use App\Models\User;
use Database\Seeders\TrackerDemoDataSeeder;
use App\Services\OperatorActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TrackerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_dashboard_shows_status_without_operator_controls(): void
    {
        $this->get(route('tracker.dashboard'))
            ->assertOk()
            ->assertSee('Solar tracker')
            ->assertSee('v0.2.1')
            ->assertSee('Latest telemetry')
            ->assertSee('Net yield (latest)')
            ->assertSee('Solar opbrengst')
            ->assertDontSee('Noodstop');
    }

    public function test_operator_controls_require_authentication(): void
    {
        $this->get(route('tracker.control'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_operator_controls_are_available(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('tracker.control'))
            ->assertOk()
            ->assertSee('Oost')
            ->assertSee('Noodstop')
            ->assertSee('Automatic')
            ->assertSee('Manual')
            ->assertSee('data-async-control', false)
            ->assertSee('data-confirm="Noodstop activeren?', false)
            ->assertSee('id="control-feedback"', false);
    }

    public function test_operator_can_be_created_from_the_console(): void
    {
        $this->artisan('tracker:create-operator')
            ->expectsQuestion('Operator name', 'Demo Operator')
            ->expectsQuestion('Operator email', 'operator@example.test')
            ->expectsQuestion('Password (at least 12 characters)', 'a-long-test-password')
            ->expectsQuestion('Confirm password', 'a-long-test-password')
            ->expectsOutput('Operator account created.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', ['email' => 'operator@example.test']);
    }

    public function test_operator_password_can_be_reset_from_the_console(): void
    {
        User::factory()->create(['email' => 'operator@example.test']);

        $this->artisan('tracker:reset-operator-password operator@example.test')
            ->expectsQuestion('New password (at least 12 characters)', 'a-new-test-password')
            ->expectsQuestion('Confirm new password', 'a-new-test-password')
            ->expectsOutput('Operator password updated.')
            ->assertExitCode(0);

        $this->assertTrue(Hash::check(
            'a-new-test-password',
            User::query()->where('email', 'operator@example.test')->firstOrFail()->password
        ));
    }

    public function test_idle_operator_is_explicitly_switched_to_automatic_mode_once(): void
    {
        config([
            'services.tracker.plc_url' => 'https://plc.example.test',
            'services.tracker.plc_command_path' => 'api/commands',
        ]);
        Cache::put(OperatorActivity::LAST_ACTIVITY_KEY, now()->subSeconds(601)->timestamp);
        Http::fake();

        $this->artisan('tracker:check-idle-mode')
            ->expectsOutput('PLC automatic mode requested after operator inactivity.')
            ->assertExitCode(0);

        Http::assertSent(fn (ClientRequest $request): bool =>
            $request->url() === 'https://plc.example.test/api/commands'
            && $request->data() === ['mode' => 'automatic', 'command' => 'set_mode']
        );

        $this->artisan('tracker:check-idle-mode')->assertExitCode(0);
        Http::assertSentCount(1);
    }

    public function test_recent_operator_activity_does_not_switch_to_automatic_mode(): void
    {
        config(['services.tracker.plc_url' => 'https://plc.example.test']);
        Cache::put(OperatorActivity::LAST_ACTIVITY_KEY, now()->subSeconds(599)->timestamp);
        Http::fake();

        $this->artisan('tracker:check-idle-mode')->assertExitCode(0);

        Http::assertNothingSent();
    }

    public function test_demo_seeder_creates_repeatable_fourteen_day_yield_history(): void
    {
        Carbon::setTestNow('2026-10-17 12:15:00');

        $this->seed(TrackerDemoDataSeeder::class);
        $this->assertDatabaseCount('tracker_logs', 672);

        $this->seed(TrackerDemoDataSeeder::class);
        $this->assertDatabaseCount('tracker_logs', 672);
        $this->assertDatabaseHas('tracker_logs', ['status' => 'demo']);

        Carbon::setTestNow();
    }

    public function test_telemetry_endpoint_requires_a_bearer_token(): void
    {
        config(['services.tracker.ingest_token' => 'telemetry-secret']);

        $this->postJson('/api/tracker/logs', [])
            ->assertUnauthorized();
    }

    public function test_api_validation_errors_are_json_without_an_accept_header(): void
    {
        config(['services.tracker.ingest_token' => 'telemetry-secret']);

        $this->call('POST', '/api/tracker/logs', [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer telemetry-secret',
            'CONTENT_TYPE' => 'application/json',
        ], '{}')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['positie_actuator_1', 'opbrengst']);
    }

    public function test_valid_telemetry_is_stored(): void
    {
        config(['services.tracker.ingest_token' => 'telemetry-secret']);

        $payload = [
            'datum_tijd' => '2026-10-17T12:30:00Z',
            'positie_actuator_1' => 42.5,
            'positie_actuator_2' => 44.0,
            'opbrengst' => 8.7,
            'status' => 'active',
            'modus' => 'automatic',
            'systeem_actief' => true,
            'noodstop_actief' => false,
            'verbruik_actuator_1' => 0.4,
            'verbruik_actuator_2' => 0.3,
            'motor_verbruik' => 0.8,
            'netto_opbrengst' => 7.2,
            'windsnelheid' => 3.1,
        ];

        $this->postJson('/api/tracker/logs', $payload, [
            'Authorization' => 'Bearer telemetry-secret',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.modus', 'automatic')
            ->assertJsonPath('data.systeem_actief', true);

        $this->assertDatabaseHas('tracker_logs', [
            'status' => 'active',
            'modus' => 'automatic',
            'systeem_actief' => true,
            'netto_opbrengst' => 7.2,
        ]);

        $this->assertFalse(app(\App\Services\TrackerSafetyState::class)->isEmergencyStopActive());
    }

    public function test_telemetry_mapping_supports_nested_external_field_paths(): void
    {
        config([
            'services.tracker.ingest_token' => 'telemetry-secret',
            'tracker.telemetry.field_map.opbrengst' => 'plant.energy.total',
            'tracker.telemetry.value_map.modus' => ['auto' => 'automatic'],
        ]);

        $payload = [
            'datum_tijd' => '2026-10-17T12:30:00Z',
            'positie_actuator_1' => 42.5,
            'positie_actuator_2' => 44.0,
            'plant' => ['energy' => ['total' => 8.7]],
            'status' => 'active',
            'modus' => 'auto',
            'systeem_actief' => true,
            'noodstop_actief' => false,
            'verbruik_actuator_1' => 0.4,
            'verbruik_actuator_2' => 0.3,
            'motor_verbruik' => 0.8,
            'netto_opbrengst' => 7.2,
            'windsnelheid' => 3.1,
        ];

        $this->postJson('/api/tracker/logs', $payload, [
            'Authorization' => 'Bearer telemetry-secret',
        ])->assertCreated()
            ->assertJsonPath('data.opbrengst', 8.7);

        $this->assertDatabaseHas('tracker_logs', ['opbrengst' => 8.7]);
    }

    public function test_authenticated_oost_command_is_forwarded_to_the_plc(): void
    {
        config([
            'services.tracker.plc_url' => 'https://plc.example.test',
            'services.tracker.plc_command_path' => 'api/commands',
            'services.tracker.plc_token' => 'plc-secret',
        ]);
        Http::fake();

        $this->actingAs(User::factory()->create())
            ->post(route('tracker.oost'))
            ->assertRedirect();

        Http::assertSent(fn (ClientRequest $request): bool =>
            $request->url() === 'https://plc.example.test/api/commands'
            && $request->data() === ['command' => 'oost']
            && $request->hasHeader('Authorization', 'Bearer plc-secret')
        );
    }

    public function test_mode_request_uses_an_explicit_mode_payload(): void
    {
        config(['services.tracker.plc_url' => 'https://plc.example.test']);
        Http::fake();

        $this->actingAs(User::factory()->create())
            ->postJson(route('tracker.mode', ['mode' => 'automatic']))
            ->assertOk()
            ->assertJsonPath('message', 'PLC accepted the automatic mode request. Waiting for telemetry confirmation.');

        Http::assertSent(fn (ClientRequest $request): bool =>
            $request->data() === ['mode' => 'automatic', 'command' => 'set_mode']
        );
    }

    public function test_idle_automation_does_not_override_emergency_stop(): void
    {
        config(['services.tracker.plc_url' => 'https://plc.example.test']);
        Cache::put(OperatorActivity::LAST_ACTIVITY_KEY, now()->subSeconds(601)->timestamp);
        Cache::forever('tracker.emergency_stop_active', true);
        Http::fake();

        $this->artisan('tracker:check-idle-mode')->assertExitCode(0);

        Http::assertNothingSent();
        Cache::forget('tracker.emergency_stop_active');
    }

    public function test_emergency_stop_and_reset_update_state_after_plc_acknowledgement(): void
    {
        config(['services.tracker.plc_url' => 'https://plc.example.test']);
        Cache::forget('tracker.emergency_stop_active');
        Http::fake();
        $operator = User::factory()->create();

        $this->actingAs($operator)
            ->postJson(route('tracker.noodstop'))
            ->assertOk()
            ->assertJsonPath('emergencyStopActive', true);

        $this->postJson(route('tracker.noodstop.reset'))
            ->assertOk()
            ->assertJsonPath('emergencyStopActive', false);

        Http::assertSent(fn (ClientRequest $request): bool => $request->data() === ['command' => 'noodstop']);
        Http::assertSent(fn (ClientRequest $request): bool => $request->data() === ['command' => 'noodstop_reset']);
    }

    public function test_rejected_emergency_stop_does_not_activate_red_alert_state(): void
    {
        config(['services.tracker.plc_url' => 'https://plc.example.test']);
        Cache::forget('tracker.emergency_stop_active');
        Http::fake(['plc.example.test/*' => Http::response([], 503)]);

        $this->actingAs(User::factory()->create())
            ->postJson(route('tracker.noodstop'))
            ->assertStatus(502);

        $this->assertFalse(app(\App\Services\TrackerSafetyState::class)->isEmergencyStopActive());
    }

    public function test_emergency_state_is_visible_on_the_public_dashboard(): void
    {
        Cache::forever('tracker.emergency_stop_active', true);

        $this->get(route('tracker.dashboard'))
            ->assertOk()
            ->assertSee('NOODSTOP ACTIEF')
            ->assertSee('emergency-active');

        Cache::forget('tracker.emergency_stop_active');
    }
}