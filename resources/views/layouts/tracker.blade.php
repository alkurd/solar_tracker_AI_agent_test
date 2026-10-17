<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Solar Tracker') · {{ config('app.name', 'Solar Tracker') }}</title>
    @vite('resources/js/app.js')
    <style>
        :root {
            color-scheme: light;
            --ink: #202923;
            --muted: #68736b;
            --paper: #edf0e9;
            --surface: #fbfcf8;
            --line: #cbd2c8;
            --green: #176b4a;
            --green-dark: #10563b;
            --amber: #dda126;
            --red: #b52c25;
            --red-dark: #8f1e19;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--ink);
            background-color: var(--paper);
            background-image: linear-gradient(rgba(32, 41, 35, .035) 1px, transparent 1px), linear-gradient(90deg, rgba(32, 41, 35, .035) 1px, transparent 1px);
            background-size: 24px 24px;
            font-family: "Avenir Next", "Segoe UI", sans-serif;
        }

        body.emergency-active::before, body.emergency-active::after {
            position: fixed;
            z-index: 30;
            right: 0;
            left: 0;
            height: 12px;
            background: #d71920;
            box-shadow: 0 0 18px 5px rgba(215, 25, 32, .8);
            content: "";
            pointer-events: none;
            animation: emergency-pulse 1.1s ease-in-out infinite alternate;
        }

        body.emergency-active::before { top: 0; }
        body.emergency-active::after { bottom: 0; }
        @keyframes emergency-pulse { from { opacity: .7; } to { opacity: 1; } }
        @media (prefers-reduced-motion: reduce) {
            body.emergency-active::before, body.emergency-active::after { animation: none; }
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 54px;
            padding: 0 24px;
            color: #f7f8f4;
            background: var(--ink);
            border-bottom: 3px solid var(--amber);
        }

        .topbar-brand, .topbar-meta, .eyebrow, .metric-label, .section-kicker {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        .topbar-brand { font-size: 12px; font-weight: 700; }
        .topbar-meta { color: #c2cbc2; font-size: 10px; }

        main { width: min(100% - 32px, 900px); margin: 30px auto 56px; }
        h1, h2, p { margin-top: 0; }
        h1 { margin-bottom: 0; font-size: 30px; line-height: 1.1; font-weight: 650; }
        h2 { margin-bottom: 14px; font-size: 16px; }
        .eyebrow, .section-kicker { margin-bottom: 7px; color: var(--muted); font-size: 10px; }

        .page-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 22px; }
        .telemetry { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); border: 1px solid var(--line); background: var(--surface); }
        .metric { min-width: 0; padding: 14px 16px; border-right: 1px solid var(--line); }
        .metric:last-child { border-right: 0; }
        .metric:nth-child(3n) { border-right: 0; }
        .metric:nth-child(-n+3) { border-bottom: 1px solid var(--line); }
        .metric-label { display: block; margin-bottom: 8px; color: var(--muted); font-size: 9px; }
        .metric-value { display: block; overflow-wrap: anywhere; font-size: 18px; font-variant-numeric: tabular-nums; font-weight: 650; }
        .metric-unit { margin-left: 3px; color: var(--muted); font-size: 11px; font-weight: 500; }
        .telemetry-note { margin: 0; padding: 10px 14px; border: 1px solid var(--line); border-top: 0; color: var(--muted); background: var(--surface); font-size: 12px; }
        .yield-section { margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--line); }
        .yield-header { display: flex; align-items: end; justify-content: space-between; gap: 16px; margin-bottom: 14px; }
        .yield-title { margin: 0; font-size: 18px; }
        .yield-tools { display: flex; flex-wrap: wrap; align-items: center; justify-content: end; gap: 6px; }
        .range-tools { display: flex; border: 1px solid var(--line); border-radius: 3px; overflow: hidden; }
        .chart-tool, .range-tool { min-width: 34px; min-height: 34px; padding: 6px 9px; border: 0; color: var(--ink); background: var(--surface); font: inherit; font-size: 12px; cursor: pointer; }
        .chart-tool { border: 1px solid var(--line); border-radius: 3px; font-size: 16px; }
        .range-tool + .range-tool { border-left: 1px solid var(--line); }
        .range-tool[aria-pressed="true"] { color: white; background: var(--green); }
        .chart-tool:hover, .range-tool:hover { background: #e4ebe2; }
        .chart-tool:focus-visible, .range-tool:focus-visible { outline: 3px solid var(--amber); outline-offset: -2px; }
        .yield-chart-frame { position: relative; height: 340px; min-height: 260px; padding: 12px 14px 8px; border: 1px solid var(--line); background: var(--surface); }
        .yield-chart-frame canvas { display: block; width: 100%; height: 100%; }
        .chart-empty { display: grid; min-height: 220px; place-items: center; border: 1px solid var(--line); color: var(--muted); background: var(--surface); font-size: 13px; }
        .page-actions { display: flex; align-items: center; gap: 8px; }
        .emergency-banner { margin-bottom: 16px; padding: 12px 16px; color: white; background: #a7191d; border: 1px solid #741216; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-weight: 800; text-align: center; }
        .mode-panel { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 26px; padding: 14px; border: 1px solid var(--line); background: var(--surface); }
        .mode-state { margin: 0; font-size: 13px; font-weight: 650; }
        .mode-selector { display: flex; gap: 6px; }
        .mode-selector .control-button { width: auto; min-height: 42px; padding: 8px 12px; font-size: 12px; }
        .mode-selector .control-button[aria-pressed="true"] { border-color: var(--green); box-shadow: inset 0 -3px var(--amber); }
        .control-button.emergency-reset { min-height: 44px; margin-top: 10px; color: var(--red-dark); border-color: var(--red); }
        .control-feedback { display: none; margin: 12px 0; padding: 11px 14px; border: 1px solid var(--line); border-left: 4px solid var(--green); background: var(--surface); font-size: 13px; }
        .control-feedback.visible { display: block; }
        .control-feedback.error { border-left-color: var(--red); }

        .control-section { margin-top: 26px; }
        .control-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .control-form { min-width: 0; }
        .control-button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 70px;
            padding: 14px;
            border: 1px solid #aab5aa;
            border-radius: 4px;
            color: var(--ink);
            background: var(--surface);
            font: inherit;
            font-size: 15px;
            font-weight: 650;
            cursor: pointer;
            text-decoration: none;
            transition: background-color .16s ease, border-color .16s ease, transform .16s ease;
        }

        .control-button:hover { border-color: var(--green); background: #f0f6f0; }
        .control-button:active { transform: translateY(1px); }
        .control-button:focus-visible, a:focus-visible, input:focus-visible { outline: 3px solid var(--amber); outline-offset: 2px; }
        .control-button.primary { color: white; border-color: var(--green); background: var(--green); }
        .control-button.primary:hover { border-color: var(--green-dark); background: var(--green-dark); }
        .control-button.emergency { grid-column: 1 / -1; min-height: 58px; color: white; border-color: var(--red); background: var(--red); }
        .control-button.emergency:hover { border-color: var(--red-dark); background: var(--red-dark); }
        .control-button.mode { min-height: 48px; margin-top: 10px; border-style: dashed; }

        .safety-note { margin: 14px 0 0; padding-left: 12px; border-left: 3px solid var(--amber); color: #4f5a52; font-size: 12px; line-height: 1.5; }
        .flash, .error-box { margin-bottom: 14px; padding: 11px 14px; border: 1px solid var(--line); background: var(--surface); font-size: 13px; }
        .flash { border-left: 4px solid var(--green); }
        .error-box { border-left: 4px solid var(--red); }
        .error-box ul { margin: 0; padding-left: 18px; }
        .quiet-button { min-height: 36px; padding: 7px 12px; border: 1px solid var(--line); border-radius: 3px; color: var(--ink); background: var(--surface); font: inherit; font-size: 12px; cursor: pointer; }
        .login-panel { width: min(100%, 420px); margin: 7vh auto 0; padding: 26px; border: 1px solid var(--line); background: var(--surface); }
        .login-panel h1 { margin-bottom: 8px; font-size: 24px; }
        .login-copy { margin-bottom: 22px; color: var(--muted); font-size: 13px; }
        .field { display: grid; gap: 6px; margin-bottom: 15px; }
        .field label { font-size: 12px; font-weight: 650; }
        .field input { width: 100%; min-height: 42px; padding: 9px 11px; border: 1px solid var(--line); border-radius: 3px; color: var(--ink); background: white; font: inherit; }
        .remember-row { display: flex; align-items: center; gap: 8px; margin: 2px 0 18px; color: var(--muted); font-size: 12px; }
        .login-button { min-height: 44px; }

        @media (max-width: 620px) {
            .topbar { padding: 0 16px; }
            main { width: min(100% - 24px, 900px); margin-top: 22px; }
            h1 { font-size: 25px; }
            .telemetry { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .metric:nth-child(odd) { border-right: 1px solid var(--line); }
            .metric:nth-child(even) { border-right: 0; }
            .metric:nth-child(-n+4) { border-bottom: 1px solid var(--line); }
            .control-button { min-height: 64px; }
            .login-panel { margin-top: 4vh; padding: 20px; }
            .yield-header { align-items: start; flex-direction: column; }
            .yield-tools { justify-content: start; }
            .yield-chart-frame { height: 290px; padding: 8px 6px 4px; }
            .mode-panel { align-items: start; flex-direction: column; }
            .mode-selector { width: 100%; }
            .mode-selector form { flex: 1; }
            .mode-selector .control-button { width: 100%; }
        }
    </style>
</head>
<body class="{{ ($emergencyStopActive ?? false) ? 'emergency-active' : '' }}">
    <div class="topbar">
        <span class="topbar-brand">ST-01 / Solar Tracker</span>
        <span class="topbar-meta">PLC Control Interface · v{{ config('app.version') }}</span>
    </div>
    <div id="emergency-banner" class="emergency-banner" role="alert" @if (!($emergencyStopActive ?? false)) hidden @endif>
        NOODSTOP ACTIEF · Beweging geblokkeerd
    </div>
    <main>
        @yield('content')
    </main>
</body>
</html>