<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
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

        {{-- SIDEBAR OVERLAY (mobil) --}}
        <div class="sidebar-overlay" id="sidebar-overlay" aria-hidden="true"></div>

        {{-- SIDEBAR --}}
        <aside class="sidebar" id="app-sidebar">
            <button type="button" class="sidebar__close" id="sidebar-close" aria-label="Yopish">&times;</button>

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
                                {{ App\Models\Company::active()?->nomi ?? '-' }} Ega
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

        <div class="main">
            <header class="topbar">
                <button type="button" class="topbar__menu" id="sidebar-toggle" aria-label="Menyu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <h1>{{ $title }} {{ App\Models\Company::active()?->nomi ?? '—' }}</h1>
                <a href="/" class="topbar__back">← Sayt</a>
            </header>

            {{ $slot }}
        </div>
    </div>

    <style>
        .sidebar__company-tag { opacity: .7; font-weight: 500; }
        .sidebar__switch-company { font-size: 13px; opacity: .85; }
    </style>

    <script>
        (function () {
            var sidebar = document.getElementById('app-sidebar');
            var overlay = document.getElementById('sidebar-overlay');
            var toggle = document.getElementById('sidebar-toggle');
            var closeBtn = document.getElementById('sidebar-close');

            function openSidebar() {
                if (!sidebar) return;
                sidebar.classList.add('is-open');
                if (overlay) overlay.classList.add('is-open');
                document.body.classList.add('sidebar-open');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                if (!sidebar) return;
                sidebar.classList.remove('is-open');
                if (overlay) overlay.classList.remove('is-open');
                document.body.classList.remove('sidebar-open');
                document.body.style.overflow = '';
            }

            function toggleSidebar() {
                if (sidebar && sidebar.classList.contains('is-open')) closeSidebar();
                else openSidebar();
            }

            if (toggle) toggle.addEventListener('click', toggleSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (overlay) overlay.addEventListener('click', closeSidebar);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeSidebar();
            });

            if (sidebar) {
                sidebar.querySelectorAll('.sidebar__link').forEach(function (link) {
                    link.addEventListener('click', function () {
                        if (window.matchMedia('(max-width: 900px)').matches) closeSidebar();
                    });
                });
            }

            function labelTables(root) {
                (root || document).querySelectorAll('table.table').forEach(function (table) {
                    var heads = [];
                    table.querySelectorAll('thead th').forEach(function (th) {
                        heads.push((th.textContent || '').trim());
                    });
                    if (!heads.length) return;
                    table.querySelectorAll('tbody tr').forEach(function (tr) {
                        var cells = tr.querySelectorAll('td');
                        cells.forEach(function (td, i) {
                            if (td.hasAttribute('colspan')) return;
                            if (!td.getAttribute('data-label') && heads[i]) {
                                td.setAttribute('data-label', heads[i]);
                            }
                        });
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () { labelTables(); });
            } else {
                labelTables();
            }

            document.addEventListener('click', function (e) {
                if (e.target.closest('.acc__head')) {
                    setTimeout(function () { labelTables(); }, 50);
                }
            });
        })();
    </script>

</body>

</html>
