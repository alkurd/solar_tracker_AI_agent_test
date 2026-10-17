@extends('layouts.tracker')

@section('title', 'Solar Tracker Status')

@section('content')
    <header class="page-head">
        <div>
            <p class="eyebrow">Live system status / ST-01</p>
            <h1>Solar tracker</h1>
        </div>
        @auth
            <a class="quiet-button" href="{{ route('tracker.control') }}">Operator controls</a>
        @else
            <a class="quiet-button" href="{{ route('login') }}">Operator sign in</a>
        @endauth
    </header>

    @include('tracker._telemetry')
    @include('tracker._yield_chart')

    <p class="safety-note">Live status is public and read-only. Tracker movement controls are restricted to authenticated operators.</p>
@endsection