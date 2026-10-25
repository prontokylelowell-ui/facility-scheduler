@extends('layouts.app')

@section('title', 'Register — Resort Booking')

@section('content')
    <div class="auth-container">
        <div class="auth-image" style="background-image: url('https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=luxurious%20resort%20pool%20area%20with%20palm%20trees%20at%20sunset%2C%20high%20quality%2C%20realistic%20photography&image_size=portrait_16_9');">
            <div class="auth-image-content">
                <div class="hero-tag" style="background: rgba(255,255,255,0.2); display: inline-block; padding: 4px 12px; border-radius: 20px; backdrop-filter: blur(4px);">
                    Your next escape starts here
                </div>
                <h2 style="font-size: 2.5rem; margin: 20px 0;">Less planning.<br>More unwinding.</h2>
                <p style="opacity: 0.9; max-width: 400px; margin-bottom: 30px;">Make room for your next getaway. Discover our resort spaces and bring your perfect stay a little closer.</p>
                
                <div style="display: flex; gap: 20px; font-size: 0.85rem; font-weight: 500;">
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Explore resort facilities
                    </span>
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Your own guest account
                    </span>
                </div>
            </div>
        </div>

        <div class="auth-form-side">
            <div class="auth-logo">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: var(--primary);" aria-hidden="true">
                    <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <span style="color: var(--primary); font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 8px;">Welcome to Resort Booking</span>
            <h1 class="auth-title">Create an account</h1>
            <p class="auth-desc">Enter your details below to set up your guest account.</p>

            @if ($errors->any())
                <div style="background: #FFF5F5; color: #C53030; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem;">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf

                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">First name</label>
                        <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" placeholder="Enter first name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last name</label>
                        <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Enter last name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <div style="position: relative;">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
                        <svg style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div style="position: relative;">
                        <input id="password" type="password" name="password" placeholder="Create a password" required>
                        <svg style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm password</label>
                    <div style="position: relative;">
                        <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Re-enter your password" required>
                        <svg style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </div>
                </div>

                <div style="margin-top: 10px;">
                    <button type="submit" style="width: 100%; justify-content: center;">
                        Register
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </button>
                </div>
            </form>

            <p style="text-align: center; margin-top: 32px; font-size: 0.9rem; color: var(--text-muted);">
                Already registered? <a href="{{ route('login') }}" style="color: var(--primary); text-decoration: none; font-weight: 600;">Sign in</a>
            </p>

            <div style="margin-top: 40px; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.75rem; color: var(--text-muted);">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                Your details are safe with us.
            </div>
        </div>
    </div>
@endsection
