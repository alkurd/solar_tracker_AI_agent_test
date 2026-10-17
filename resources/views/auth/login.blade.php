@extends('layouts.tracker')

@section('title', 'Sign In')

@section('content')
    <section class="login-panel">
        <p class="eyebrow">Restricted access / ST-01</p>
        <h1>Sign in</h1>
        <p class="login-copy">Authenticate to access the solar tracker controls.</p>

        @if ($errors->any())
            <div class="error-box" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <div class="field">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <label class="remember-row">
                <input type="checkbox" name="remember" value="1">
                Remember me
            </label>
            <button class="control-button primary login-button" type="submit">Sign in</button>
        </form>
    </section>
@endsection