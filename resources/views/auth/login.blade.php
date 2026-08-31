<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirish — Boshqaruv</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

    <div class="login-page">
        <div class="login-frame">

            {{-- CHAP PANEL — BREND --}}
            <div class="login-side">
                <div class="login-side__brand">
                    <span class="login-side__mark">E</span>
                    <span class="login-side__brand-text">
                        ERKAPOY
                        <small>Ishlab chiqarish</small>
                    </span>
                </div>

                <div class="login-side__body">
                    <div class="login-side__quote-mark">“</div>
                    <h1>Har bir tikuvda mehr, har bir hisobda aniqlik.</h1>
                    <p>Mahsulotlar, xarajatlar va buyurtmalarni bir joydan — aniq va tartibli boshqaring.</p>
                </div>

                <div class="login-side__footer">
                    <div class="login-side__stats">
                        <div>
                            <div class="login-side__stat-num">24</div>
                            <div class="login-side__stat-label">Mahsulot</div>
                        </div>
                        <div>
                            <div class="login-side__stat-num">57</div>
                            <div class="login-side__stat-label">Buyurtma</div>
                        </div>
                        <div>
                            <div class="login-side__stat-num">3</div>
                            <div class="login-side__stat-label">Yangi</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- O'NG PANEL — FORMA --}}
            <div class="login-form-side">
                <div class="login-card">
                    <p class="login-card__eyebrow">Xush kelibsiz</p>
                    <h2>Tizimga kirish</h2>
                    <p class="login-card__sub">Davom etish uchun hisobingizga kiring.</p>

                    @if ($errors->any())
                        <div class="login-alert">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0; margin-top:1px;">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}">
                        @csrf

                        <div class="field">
                            <label for="email">Email</label>
                            <div class="input-wrap">
                                <span class="input-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/>
                                    </svg>
                                </span>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="email@misol.uz">
                            </div>
                            @error('email')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="password">Parol</label>
                            <div class="input-wrap">
                                <span class="input-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                    </svg>
                                </span>
                                <input type="password" id="password" name="password" required placeholder="••••••••">
                            </div>
                            @error('password')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field-row">
                            <label class="checkbox">
                                <input type="checkbox" name="remember">
                                Meni eslab qol
                            </label>
                            <a href="#" class="link-muted">Parolni unutdingizmi?</a>
                        </div>

                        <button type="submit" class="btn-primary">
                            Kirish
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                            </svg>
                        </button>
                    </form>

                    <p class="login-card__footer">
                        Hisobingiz yo'qmi? <a href="#" class="link-muted">Bog'laning</a>
                    </p>
                </div>
            </div>

        </div>
    </div>

</body>
</html>