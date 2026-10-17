@extends('layouts.tracker')

@section('title', 'Operator Controls')

@section('content')
    <header class="page-head">
        <div>
            <p class="eyebrow">Restricted operator panel / ST-01</p>
            <h1>Tracker controls</h1>
        </div>
        <div class="page-actions">
            <a class="quiet-button" href="{{ route('tracker.dashboard') }}">Public status</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="quiet-button" type="submit">Log out</button>
            </form>
        </div>
    </header>

    @if (session('status'))
        <div class="flash" role="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="error-box" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="control-feedback" id="control-feedback" role="status" aria-live="polite"></div>

    @include('tracker._telemetry')
    @include('tracker._yield_chart')

    <section class="mode-panel" aria-label="Operating mode">
        <div>
            <p class="metric-label">PLC-reported mode</p>
            <p class="mode-state" id="current-mode">{{ $operatingMode ? ucfirst($operatingMode) : 'Unknown · waiting for telemetry' }}</p>
        </div>
        <div class="mode-selector" role="group" aria-label="Set operating mode">
            <form method="POST" action="{{ route('tracker.mode', ['mode' => 'automatic']) }}" data-async-control>
                @csrf
                <button class="control-button" type="submit" aria-pressed="{{ $operatingMode === 'automatic' ? 'true' : 'false' }}">Automatic</button>
            </form>
            <form method="POST" action="{{ route('tracker.mode', ['mode' => 'manual']) }}" data-async-control>
                @csrf
                <button class="control-button" type="submit" aria-pressed="{{ $operatingMode === 'manual' ? 'true' : 'false' }}">Manual</button>
            </form>
        </div>
    </section>

    <section class="control-section" aria-labelledby="manual-controls">
        <p class="section-kicker">Operator actions</p>
        <h2 id="manual-controls">Manual control</h2>
        <div class="control-grid">
            <form class="control-form" method="POST" action="{{ route('tracker.oost') }}" data-async-control>
                @csrf
                <button class="control-button primary" type="submit">Oost</button>
            </form>
            <form class="control-form" method="POST" action="{{ route('tracker.west') }}" data-async-control>
                @csrf
                <button class="control-button" type="submit">West</button>
            </form>
            <form class="control-form" method="POST" action="{{ route('tracker.poot_omhoog') }}" data-async-control>
                @csrf
                <button class="control-button primary" type="submit">Poot Omhoog</button>
            </form>
            <form class="control-form" method="POST" action="{{ route('tracker.poot_omlaag') }}" data-async-control>
                @csrf
                <button class="control-button" type="submit">Poot Omlaag</button>
            </form>
            <form class="control-form" method="POST" action="{{ route('tracker.noodstop') }}" data-async-control data-confirm="Noodstop activeren? De tracker stopt pas nadat de PLC de opdracht bevestigt.">
                @csrf
                <button class="control-button emergency" type="submit" data-emergency-stop>Noodstop</button>
            </form>
        </div>
        <form method="POST" action="{{ route('tracker.noodstop.reset') }}" data-async-control data-confirm="Noodstop resetten? Controleer eerst dat de tracker en omgeving veilig zijn." @if (!$emergencyStopActive) hidden @endif>
            @csrf
            <button class="control-button emergency-reset" type="submit" data-emergency-reset>Reset Noodstop</button>
        </form>
        <p class="safety-note">Actuator travel is limited to 35°–55°. Verify PLC interlocks and physical end stops before operation; the PLC must enforce these limits independently.</p>
    </section>
@endsection