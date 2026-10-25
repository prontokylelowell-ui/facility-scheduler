@extends('layouts.app')

@section('title', 'Login — Resort Booking')

@section('content')
    <div class="auth-container">
        <div class="auth-image" style="background-image: url('https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=luxury%20resort%20villa%20with%20private%20pool%20overlooking%20the%20ocean%2C%20serene%20atmosphere%2C%20high%20quality%2C%20professional%20photography&image_size=portrait_16_9');">
            <div class="auth-image-content">
                <div class="hero-tag" style="background: rgba(255,255,255,0.2); display: inline-block; padding: 4px 12px; border-radius: 20px; backdrop-filter: blur(4px);">
                    Welcome back to paradise
                </div>
                <h2 style="font-size: 2.5rem; margin: 20px 0;">Find your space.<br>Enjoy your stay.</h2>
                <p style="opacity: 0.9; max-width: 400px; margin-bottom: 30px;">Sign in to access your bookings and explore our exclusive resort facilities.</p>
                
                <div style="display: flex; gap: 20px; font-size: 0.85rem; font-weight: 500;">
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Easy booking management
                    </span>
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Exclusive resort access
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

            <span style="color: var(--primary); font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 8px;">Resort Booking System</span>
            <h1 class="auth-title">Sign in</h1>
            <p class="auth-desc">Enter your credentials to access your account.</p>

            @if ($errors->any())
                <div style="background: #FFF5F5; color: #C53030; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem;">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="email">Email address</label>
                    <div style="position: relative;">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                        <svg style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div style="position: relative;">
                        <input id="password" type="password" name="password" placeholder="Enter your password" required>
                        <svg style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </div>
                </div>

                <div style="margin-top: 10px;">
                    <button type="submit" style="width: 100%; justify-content: center;">
                        Sign in
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </button>
                </div>
            </form>

            <p style="text-align: center; margin-top: 32px; font-size: 0.9rem; color: var(--text-muted);">
                Don't have an account? <a href="{{ route('register') }}" style="color: var(--primary); text-decoration: none; font-weight: 600;">Register here</a>
            </p>

            <div style="margin-top: 40px; padding: 20px; background: var(--bg-light); border-radius: 12px; font-size: 0.8rem;">
                <div style="font-weight: 700; color: var(--text-dark); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    Demo accounts (seeded):
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px; color: var(--text-muted);">
                    <span><strong>Admin:</strong> admin@pup.edu.ph / admin123</span>
                    <span><strong>Guest:</strong> juan.delacruz@gmail.com / password123</span>
                </div>
            </div>
        </div>
    </div>
@endsection
