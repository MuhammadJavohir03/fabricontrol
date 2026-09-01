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
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('css/products.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('css/companies.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}?v=4">
    <link rel="icon" href="{{ asset('img/logo.png') }}" type="image/x-icon">
</head>

<body>
    <div class="app-shell">

        <div class="sidebar-overlay" id="sidebar-overlay" onclick="window.closeAppSidebar && window.closeAppSidebar()">
        </div>

        <aside class="sidebar" id="app-sidebar">
            <button type="button" class="sidebar__close" onclick="window.closeAppSidebar && window.closeAppSidebar()"
                aria-label="Yopish">&times;</button>

            <div class="sidebar__brand">
                <span
                    class="sidebar__mark">{{ App\Models\Company::active()?->nomi ? substr(App\Models\Company::active()?->nomi, 0, 1) : 'S' }}</span>
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
                    class="sidebar__link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"
                    onclick="window.closeAppSidebar && window.closeAppSidebar()">
                    <span class="icon">◧</span> <span>Statistika</span>
                </a>
                <a href="{{ route('admin.products.index') }}"
                    class="sidebar__link {{ request()->routeIs('admin.products.*') ? 'is-active' : '' }}"
                    onclick="window.closeAppSidebar && window.closeAppSidebar()">
                    <span class="icon">▤</span> <span>Mahsulotlar</span>
                </a>
                <a href="{{ route('admin.xodimlar.index') }}"
                    class="sidebar__link {{ request()->routeIs('admin.xodimlar.*', 'admin.chevar-*') ? 'is-active' : '' }}"
                    onclick="window.closeAppSidebar && window.closeAppSidebar()">
                    <span class="icon">◎</span> <span>Xodimlar</span>
                </a>
                @auth
                    @if (auth()->user()->role === 'super_admin')
                        <a href="{{ route('admin.companies.index') }}"
                            class="sidebar__link {{ request()->routeIs('admin.companies.*') ? 'is-active' : '' }}"
                            onclick="window.closeAppSidebar && window.closeAppSidebar()">
                            <span class="icon">⌂</span> <span>Companiyalar</span>
                        </a>
                    @endif
                @endauth
                <a href="{{ route('admin.buyurtmalar.index') }}"
                    class="sidebar__link {{ request()->routeIs('admin.buyurtmalar.*') ? 'is-active' : '' }}"
                    onclick="window.closeAppSidebar && window.closeAppSidebar()">
                    <span class="icon">▥</span> <span>Buyurtmalar</span>
                    @php
                        $__yangiBuyurtma = 0;
                        try {
                            $__cid = \App\Models\Company::activeId();
                            if ($__cid) {
                                $__yangiBuyurtma = \App\Models\Buyurtma::where('company_id', $__cid)
                                    ->where('holat', 'yangi')
                                    ->count();
                            }
                        } catch (\Throwable $e) {
                        }
                    @endphp
                    @if ($__yangiBuyurtma > 0)
                        <span class="sidebar__badge">{{ $__yangiBuyurtma }}</span>
                    @endif
                </a>
                <div class="sidebar__section-label">Sozlamalar</div>
                <a href="{{ route('admin.profile.edit') }}"
                    class="sidebar__link {{ request()->routeIs('admin.profile.*') ? 'is-active' : '' }}"
                    onclick="window.closeAppSidebar && window.closeAppSidebar()">
                    <span class="icon">✎</span> <span>Profil</span>
                </a>
            </nav>

            <div class="sidebar__foot">
                @auth
                    <div class="sidebar__user">
                        <strong>{{ auth()->user()->toliq_ism ?? (auth()->user()->name ?? 'Admin') }}</strong>
                    </div>
                    @if (auth()->user()->role === 'super_admin' && \App\Models\Company::active())
                        <a href="{{ route('admin.companies.index') }}" class="sidebar__link sidebar__switch-company"
                            onclick="window.closeAppSidebar && window.closeAppSidebar()">
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
                <button type="button" class="topbar__menu" id="sidebar-toggle"
                    onclick="window.toggleAppSidebar && window.toggleAppSidebar(); return false;" aria-label="Menyu">
                    <span></span><span></span><span></span>
                </button>
                <h1>{{ $title }} {{ App\Models\Company::active()?->nomi ?? '—' }}</h1>
                <a href="/" class="topbar__back">← Sayt</a>
            </header>
            {{ $slot }}
        </div>
    </div>

    <style>
        .sidebar__company-tag {
            opacity: .7;
            font-weight: 500;
        }

        .sidebar__switch-company {
            font-size: 13px;
            opacity: .85;
        }
    </style>

    <script>
        /* Sidebar — window ga bog'langan, boshqa skriptlar buzolmaydi */
        window.openAppSidebar = function() {
            var s = document.getElementById('app-sidebar');
            var o = document.getElementById('sidebar-overlay');
            if (s) s.classList.add('is-open');
            if (o) o.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        };
        window.closeAppSidebar = function() {
            var s = document.getElementById('app-sidebar');
            var o = document.getElementById('sidebar-overlay');
            if (s) s.classList.remove('is-open');
            if (o) o.classList.remove('is-open');
            document.body.style.overflow = '';
        };
        window.toggleAppSidebar = function() {
            var s = document.getElementById('app-sidebar');
            if (s && s.classList.contains('is-open')) window.closeAppSidebar();
            else window.openAppSidebar();
        };

        /* Jadvallarga data-label — mobil card uchun */
        function labelAllTables() {
            document.querySelectorAll('table.table').forEach(function(table) {
                var heads = [];
                table.querySelectorAll('thead th').forEach(function(th) {
                    heads.push((th.textContent || '').replace(/\s+/g, ' ').trim());
                });
                if (!heads.length) return;
                table.querySelectorAll('tbody tr').forEach(function(tr) {
                    var cells = tr.children;
                    for (var i = 0; i < cells.length; i++) {
                        var td = cells[i];
                        if (td.tagName !== 'TD') continue;
                        if (td.hasAttribute('colspan')) continue;
                        if (!td.getAttribute('data-label') && heads[i]) {
                            td.setAttribute('data-label', heads[i]);
                        }
                    }
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            labelAllTables();
            setTimeout(labelAllTables, 300);
            setTimeout(labelAllTables, 1000);
        });
        document.addEventListener('click', function(e) {
            if (e.target.closest && e.target.closest('.acc__head')) {
                setTimeout(labelAllTables, 100);
            }
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') window.closeAppSidebar();
        });
    </script>
</body>

</html>
