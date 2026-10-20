@extends('layouts.app')

@section('title', 'Login — Resort Booking')

@section('content')
    <div class="card" style="max-width:430px; margin:0 auto;">
        <h1>Sign in</h1>

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <div style="margin-top:18px;">
                <button type="submit">Sign in</button>
            </div>
        </form>

        <p class="muted" style="margin-top:16px;">
            No account? <a href="{{ route('register') }}">Register here</a>.
        </p>

        <p class="muted" style="margin-top:16px; border-top:1px solid #edf2f7; padding-top:12px;">
            Demo accounts (seeded):<br>
            <strong>admin@pup.edu.ph</strong> / admin123 — app_admin<br>
            <strong>juan.delacruz@gmail.com</strong> / password123 — app_user
        </p>
    </div>
@endsection
