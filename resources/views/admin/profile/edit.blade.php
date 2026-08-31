<x-layouts.sidebar title="Profil">

    <div class="content">

        @if (session('success'))
            <div class="alert alert--success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert--error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert--error">
                <ul style="margin:0;padding-left:18px;">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card table-card">
            <div class="tape-edge tape-edge--accent"></div>
            <div class="card__head" style="padding:18px 22px 0;">
                <h2>Profilni tahrirlash</h2>
                <a href="{{ route('admin.dashboard') }}" class="card__link">← Orqaga</a>
            </div>

            <form action="{{ route('admin.profile.update') }}" method="POST" style="padding:18px 22px 24px;">
                @csrf
                @method('PUT')

                <div class="form-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="form-field">
                        <label>To‘liq ism</label>
                        <input type="text"
                               name="toliq_ism"
                               value="{{ old('toliq_ism', $user->toliq_ism) }}"
                               required
                               autocomplete="name">
                    </div>
                    <div class="form-field">
                        <label>Email</label>
                        <input type="email"
                               name="email"
                               value="{{ old('email', $user->email) }}"
                               required
                               autocomplete="email">
                    </div>
                </div>

                <hr class="modal__divider">

                <div class="form-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="form-field">
                        <label>Yangi parol <small style="font-weight:500;opacity:.7;">(ixtiyoriy)</small></label>
                        <input type="password"
                               name="password"
                               autocomplete="new-password"
                               placeholder="Bo‘sh qoldiring — o‘zgarmaydi">
                    </div>
                    <div class="form-field">
                        <label>Parolni tasdiqlash</label>
                        <input type="password"
                               name="password_confirmation"
                               autocomplete="new-password"
                               placeholder="Yangi parolni qayta yozing">
                    </div>
                </div>

                <div style="margin-top:24px;display:flex;gap:10px;justify-content:flex-end;">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn--ghost">Bekor</a>
                    <button type="submit" class="btn btn--primary">Saqlash</button>
                </div>
            </form>
        </div>

    </div>

</x-layouts.sidebar>