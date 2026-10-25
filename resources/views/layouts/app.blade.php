<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Resort Booking')</title>
    <style>
        :root {
            --primary: #1D6B63;
            --primary-dark: #154D47;
            --primary-light: #E9F2F1;
            --text-dark: #1f2933;
            --text-muted: #4E6C8A;
            --bg-light: #F5F7FA;
            --white: #ffffff;
            --border: #E4E7EB;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --radius: 12px;
        }

        * { box-sizing: border-box; }
        body { 
            margin: 0; 
            font-family: 'Inter', system-ui, -apple-system, sans-serif; 
            background: var(--bg-light); 
            color: var(--text-dark); 
            line-height: 1.5;
        }

        /* Header */
        header { 
            background: var(--white); 
            padding: 16px 40px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        header .left-nav { display: flex; align-items: center; gap: 40px; }
        header .brand-wrapper { display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit; }
        header .brand { font-weight: 700; font-size: 1.2rem; color: var(--primary); }
        header nav a { 
            color: var(--text-muted); 
            text-decoration: none; 
            font-weight: 500; 
            font-size: 0.95rem;
            padding: 8px 0;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        header nav a:hover, header nav a.active { color: var(--primary); border-bottom-color: var(--primary); }

        header .user-nav { display: flex; align-items: center; gap: 20px; }
        .user-profile { display: flex; align-items: center; gap: 12px; }
        .user-avatar { 
            width: 36px; height: 36px; 
            background: var(--primary-light); 
            color: var(--primary); 
            border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            font-weight: 700; font-size: 0.85rem;
        }
        .user-info { display: flex; flex-direction: column; }
        .user-name { font-weight: 600; font-size: 0.9rem; }
        .user-role { font-size: 0.75rem; color: var(--text-muted); }

        .btn-logout {
            background: transparent;
            color: var(--text-muted);
            border: none;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .btn-logout:hover { background: #f0f2f5; color: var(--text-dark); }

        /* Layout */
        main { max-width: 1200px; margin: 0 auto; padding: 40px 20px; min-height: calc(100vh - 160px); }
        footer { 
            padding: 40px 20px; 
            background: var(--white); 
            border-top: 1px solid var(--border);
            text-align: center;
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        .footer-content { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; }

        /* Cards & Sections */
        .card { 
            background: var(--white); 
            border-radius: var(--radius); 
            padding: 30px; 
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        /* Typography */
        h1 { font-size: 1.8rem; font-weight: 700; margin-bottom: 8px; color: var(--primary-dark); }
        .section-desc { color: var(--text-muted); margin-bottom: 30px; }

        /* Forms */
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group.full { grid-column: span 2; }
        label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; color: var(--text-dark); }
        input, select, textarea { 
            width: 100%; 
            padding: 12px 16px; 
            border: 1px solid var(--border); 
            border-radius: 8px; 
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }
        input:focus, select:focus { outline: none; border-color: var(--primary); }
        
        button { 
            padding: 12px 24px; 
            border: none; 
            border-radius: 8px; 
            background: var(--primary); 
            color: var(--white); 
            font-weight: 600; 
            cursor: pointer; 
            display: inline-flex; align-items: center; gap: 8px;
            transition: background 0.2s;
        }
        button:hover { background: var(--primary-dark); }
        button.ghost { background: transparent; color: var(--text-muted); border: 1px solid var(--border); }
        button.ghost:hover { background: #f9fafb; color: var(--text-dark); }

        /* Tables */
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { 
            text-align: left; 
            padding: 16px; 
            background: #F9FAFB; 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 0.05em; 
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
        }
        td { padding: 16px; border-bottom: 1px solid var(--border); font-size: 0.9rem; }
        tr:last-child td { border-bottom: none; }
        
        .status-dot { 
            display: inline-flex; align-items: center; gap: 6px; 
            font-size: 0.85rem; font-weight: 500;
        }
        .dot { width: 8px; height: 8px; border-radius: 50%; }
        .dot-green { background: #10B981; }
        .dot-red { background: #EF4444; }

        /* Hero Banner */
        .hero-banner {
            display: flex;
            background: #0A2540;
            border-radius: var(--radius);
            overflow: hidden;
            color: var(--white);
            margin-bottom: 40px;
        }
        .hero-content { flex: 1; padding: 60px; display: flex; flex-direction: column; justify-content: center; }
        .hero-image { flex: 1; background-size: cover; background-position: center; min-height: 300px; }
        .hero-tag { text-transform: uppercase; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.1em; margin-bottom: 16px; opacity: 0.8; }
        .hero-title { font-size: 2.4rem; font-weight: 700; margin-bottom: 16px; }
        .hero-desc { font-size: 1.1rem; opacity: 0.8; max-width: 500px; }

        /* Auth Layout */
        .auth-container { display: flex; min-height: 80vh; background: var(--white); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow); }
        .auth-image { flex: 1; background-size: cover; background-position: center; position: relative; padding: 60px; display: flex; align-items: flex-end; }
        .auth-image::before { content: ''; position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.7), transparent); }
        .auth-image-content { position: relative; color: var(--white); z-index: 1; }
        .auth-form-side { flex: 1; padding: 60px; display: flex; flex-direction: column; justify-content: center; }
        .auth-logo { margin-bottom: 40px; }
        .auth-title { font-size: 2rem; font-weight: 700; margin-bottom: 8px; }
        .auth-desc { color: var(--text-muted); margin-bottom: 32px; }

        /* Utility */
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .align-center { align-items: center; }
        .gap-10 { gap: 10px; }

        /* Alerts */
        .alert { padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; font-weight: 500; border: 1px solid transparent; }
        .alert-success { background: #ECFDF5; color: #065F46; border-color: #A7F3D0; }
        .alert-error { background: #FEF2F2; color: #991B1B; border-color: #FECACA; }
    </style>
</head>
<body>
<header>
    <div class="left-nav">
        <a href="/" class="brand-wrapper">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="brand">Resort Booking</span>
        </a>
        @auth
            <nav aria-label="Main">
                <a href="{{ route('facilities.index') }}" class="{{ request()->routeIs('facilities.*') ? 'active' : '' }}">Facilities</a>
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('bookings.index') }}" class="{{ request()->routeIs('bookings.*') ? 'active' : '' }}" style="margin-left: 20px;">Admin Workspace</a>
                @else
                    <a href="{{ route('bookings.index') }}" class="{{ request()->routeIs('bookings.*') ? 'active' : '' }}" style="margin-left: 20px;">My Bookings</a>
                @endif
            </nav>
        @endauth
    </div>
    
    <div class="user-nav">
        @auth
            <div class="user-profile">
                <div class="user-avatar" aria-hidden="true">
                    {{ strtoupper(substr(Auth::user()->first_name, 0, 1) . substr(Auth::user()->last_name, 0, 1)) }}
                </div>
                <div class="user-info">
                    <span class="user-name">{{ Auth::user()->full_name }}</span>
                    <span class="user-role">{{ Auth::user()->role?->name ?? 'Guest' }}</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">
                    Logout
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn-logout">Login</a>
            <a href="{{ route('register') }}" style="text-decoration: none;">
                <button>Register</button>
            </a>
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

    @yield('content')
</main>

<footer>
    <div class="footer-content">
        <span>&copy; Resort Booking</span>
        <span>Need a hand? <span style="color: var(--primary); font-weight: 500;">Contact reception</span></span>
    </div>
</footer>
</body>
</html>
