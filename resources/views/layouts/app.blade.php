<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Facility Scheduler')</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background: #f4f6f8; color: #1f2933; }
        header { background: #1f3a5f; color: #fff; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
        header a { color: #cfe0f5; text-decoration: none; margin-right: 16px; font-weight: 500; }
        header a:hover { color: #fff; }
        header .brand { font-weight: 700; font-size: 1.05rem; color: #fff; }
        .user-chip { background: #2c5282; padding: 6px 12px; border-radius: 999px; font-size: .82rem; }
        .role-tag { background: #edf2f7; color: #1f3a5f; padding: 2px 8px; border-radius: 999px; font-size: .72rem; font-weight: 700; margin-left: 8px; }
        main { max-width: 1080px; margin: 24px auto; padding: 0 20px 60px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
        h1 { font-size: 1.4rem; margin: 0 0 16px; }
        h2 { font-size: 1.05rem; margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        th, td { text-align: left; padding: 9px 10px; border-bottom: 1px solid #edf2f7; vertical-align: top; }
        th { background: #f7fafc; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; color: #52606d; }
        label { display: block; font-size: .8rem; font-weight: 600; margin: 10px 0 4px; color: #3e4c59; }
        input, select, textarea { width: 100%; padding: 8px 10px; border: 1px solid #cbd2d9; border-radius: 6px; font: inherit; background: #fff; }
        button { padding: 9px 16px; border: 0; border-radius: 6px; background: #2c5282; color: #fff; font-weight: 600; cursor: pointer; font-size: .88rem; }
        button:hover { background: #1f3a5f; }
        button.danger { background: #c53030; }
        button.danger:hover { background: #9b2c2c; }
        button.ghost { background: #e2e8f0; color: #1f2933; }
        button.ghost:hover { background: #cbd2d9; }
        .btn-sm { padding: 5px 10px; font-size: .78rem; }
        .row { display: flex; gap: 14px; flex-wrap: wrap; }
        .row > div { flex: 1; min-width: 180px; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; font-size: .9rem; }
        .alert-success { background: #e6fffa; border: 1px solid #81e6d9; color: #234e52; }
        .alert-error { background: #fff5f5; border: 1px solid #feb2b2; color: #742a2a; }
        .errors { background: #fff5f5; border: 1px solid #feb2b2; border-radius: 8px; padding: 12px 16px; margin-bottom: 18px; color: #742a2a; font-size: .88rem; }
        .errors ul { margin: 6px 0 0; padding-left: 20px; }
        .badge { padding: 2px 9px; border-radius: 999px; font-size: .74rem; font-weight: 700; text-transform: capitalize; }
        .badge-pending { background: #fefcbf; color: #744210; }
        .badge-approved { background: #c6f6d5; color: #22543d; }
        .badge-rejected { background: #fed7d7; color: #822727; }
        .badge-cancelled { background: #e2e8f0; color: #4a5568; }
        .muted { color: #7b8794; font-size: .82rem; }
        .actions { display: flex; gap: 6px; flex-wrap: wrap; }
        .pagination { margin-top: 16px; }
        .inline-form { display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap; }
    </style>
</head>
<body>
<header>
    <div>
        <span class="brand">Facility Scheduler</span>
        @auth
            <a href="{{ route('facilities.index') }}">Facilities</a>
            <a href="{{ route('bookings.index') }}">Bookings</a>
        @endauth
    </div>
    <div>
        @auth
            <span class="user-chip">
                {{ Auth::user()->full_name }}
                <span class="role-tag">{{ Auth::user()->role?->name ?? 'no-role' }}</span>
            </span>
            <form action="{{ route('logout') }}" method="POST" style="display:inline; margin-left:10px;">
                @csrf
                <button class="ghost btn-sm" type="submit">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}">Login</a>
            <a href="{{ route('register') }}">Register</a>
        @endauth
    </div>
</header>

<main>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="errors">
            <strong>{{ $errors->count() }} validation error(s):</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>
