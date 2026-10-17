<section aria-label="Latest tracker telemetry">
    <p class="section-kicker">Latest telemetry</p>
    <div class="telemetry">
        <div class="metric">
            <span class="metric-label">Actuator 1</span>
            <span class="metric-value">{{ $latestLog ? number_format($latestLog->positie_actuator_1, 1) : '--' }}</span>
        </div>
        <div class="metric">
            <span class="metric-label">Actuator 2</span>
            <span class="metric-value">{{ $latestLog ? number_format($latestLog->positie_actuator_2, 1) : '--' }}</span>
        </div>
        <div class="metric">
            <span class="metric-label">Net yield (latest)</span>
            <span class="metric-value">{{ $latestLog ? number_format($latestLog->netto_opbrengst, 2) : '--' }}</span>
        </div>
        <div class="metric">
            <span class="metric-label">Wind speed</span>
            <span class="metric-value">{{ $latestLog ? number_format($latestLog->windsnelheid, 1) : '--' }}</span>
        </div>
        <div class="metric">
            <span class="metric-label">Mode</span>
            <span class="metric-value">{{ $operatingMode ? ucfirst($operatingMode) : 'Unknown' }}</span>
        </div>
        <div class="metric">
            <span class="metric-label">System</span>
            <span class="metric-value">{{ is_bool($systemActive) ? ($systemActive ? 'Active' : 'Stopped') : 'Unknown' }}</span>
        </div>
    </div>
    <p class="telemetry-note">
        @if ($latestLog)
            Status: <strong>{{ $latestLog->status }}</strong> · Updated {{ $latestLog->datum_tijd->format('Y-m-d H:i:s') }}
        @else
            No telemetry has been received yet.
        @endif
    </p>
</section>