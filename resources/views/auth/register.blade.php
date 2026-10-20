@extends('layouts.app')

@section('title', 'Register — Facility Scheduler')

@section('content')
    <div class="card" style="max-width:430px; margin:0 auto;">
        <h1>Create an account</h1>
        <p class="muted">New accounts are always created with the <strong>app_user</strong> role.</p>

        <form action="{{ route('register') }}" method="POST">
            @csrf

            <div class="row">
                <div>
                    <label for="first_name">First name</label>
                    <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required>
                </div>
                <div>
                    <label for="last_name">Last name</label>
                    <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required>
                </div>
            </div>

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>

            <div style="margin-top:18px;">
                <button type="submit">Register</button>
            </div>
        </form>

        <p class="muted" style="margin-top:16px;">
            Already registered? <a href="{{ route('login') }}">Sign in</a>.
        </p>
    </div>
@endsection
