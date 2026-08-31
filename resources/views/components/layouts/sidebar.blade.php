<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | {{ App\Models\Company::active()?->nomi ?? '...' }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
    href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap"
    rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/products.css') }}">
    <link rel="stylesheet" href="{{ asset('css/companies.css') }}">
    <link rel="icon" href="{{ asset('img/erka_poy_profile.png') }}" type="image/x-icon">
</head>

<body>

    <div class="app-shell">

        {{-- SIDEBAR --}}
        <aside class="sidebar">
            <div class="sidebar__brand">
                <span class="sidebar__mark">{{ App\Models\Company::active()?->nomi ? substr(App\Models\Company::active()?->nomi, 0, 1) : 'S' }}</span>
                <span class="sidebar__brand-text">
                    {{ App\Models\Company::active()?->nomi ?? 'Erka Poy' }}
                    <small>
                        @auth
                            @if (auth()->user()->role === 'super_admin')
                                Bosh Admin
                            @elseif (auth()->user()->role === 'admin')
                                Admin
                            @elseif (auth()->user()->role === 'chevar')
                                Chevar
                            @elseif (auth()->user()->role === 'ega')
                                {{App\Models\Company::active()?->nomi ?? '-'}}Ega
                            @else
                                Mijoz
                            @endif

                            @if ($__activeCompany = \App\Models\Company::active())
                                <span class="sidebar__company-tag">· {{ $__activeCompany->nomi }}</span>
                            @endif
                        @else
                            Mehmon
                        @endauth
                    </small>
                </span>
            </div>

            <nav class="sidebar__nav">
                <a href="{{ route('admin.dashboard') }}"
                   class="sidebar__link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                    <span class="icon">◧</span> <span>Statistika</span>
                </a>

                <a href="{{ route('admin.products.index') }}"
                   class="sidebar__link {{ request()->routeIs('admin.products.*') ? 'is-active' : '' }}">
                    <span class="icon">▤</span> <span>Mahsulotlar</span>
                </a>

                <a href="{{ route('admin.xodimlar.index') }}"
                   class="sidebar__link {{ request()->routeIs('admin.xodimlar.*', 'admin.chevar-*') ? 'is-active' : '' }}">
                    <span class="icon">◎</span> <span>Xodimlar</span>
                </a>

                @auth
                    @if (auth()->user()->role === 'super_admin')
                        <a href="{{ route('admin.companies.index') }}"
                           class="sidebar__link {{ request()->routeIs('admin.companies.*') ? 'is-active' : '' }}">
                            <span class="icon">⌂</span> <span>Companiyalar</span>
                        </a>
                    @endif
                @endauth

                <a href="#" class="sidebar__link">
                    <span class="icon">▥</span> <span>Buyurtmalar</span>
                    @if (($newOrdersCount ?? 0) > 0)
                        <span class="sidebar__badge">{{ $newOrdersCount }}</span>
                    @endif
                </a>

                <div class="sidebar__section-label">Sozlamalar</div>
                <a href="#" class="sidebar__link">
                    <span class="icon">✎</span> <span>Profil</span>
                </a>
            </nav>

            {{-- KIRISH / CHIQISH --}}
            <div class="sidebar__foot">
                @auth
                    <div class="sidebar__user">
                        <strong>{{ auth()->user()->toliq_ism ?? (auth()->user()->name ?? 'Admin') }}</strong>
                    </div>

                    @if (auth()->user()->role === 'super_admin' && \App\Models\Company::active())
                        <a href="{{ route('admin.companies.index') }}" class="sidebar__link sidebar__switch-company">
                            <span class="icon">⇄</span> <span>Companiyani almashtirish</span>
                        </a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="sidebar__link sidebar__logout">
                            <span class="icon">⏻</span> <span>Chiqish</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="sidebar__link">
                        <span class="icon">⏻</span> <span>Kirish</span>
                    </a>
                @endauth
            </div>
        </aside>

        {{-- MAIN --}}
        <div class="main">
            <header class="topbar">
                <h1>{{ $title}} {{ App\Models\Company::active()?->nomi ?? '—' }}</h1>
                <a href="/" class="topbar__back">← Saytga qaytish</a>
            </header>

            {{ $slot }}
        </div>
    </div>

    <style>
        .sidebar__company-tag { opacity: .7; font-weight: 500; }
        .sidebar__switch-company { font-size: 13px; opacity: .85; }
    </style>

</body>

</html>