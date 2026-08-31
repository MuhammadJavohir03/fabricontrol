<x-layouts.sidebar title="Profil">

    <div class="content">

        @if (session('success'))
            <div class="alert alert--success">{{ session('success') }}</div>
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

        <div class="profile-wrap">

            <div class="profile-hero">
                <div class="profile-avatar">
                    {{ mb_strtoupper(mb_substr($user->toliq_ism ?? 'A', 0, 1)) }}
                </div>
                <h2>{{ $user->toliq_ism ?? 'Foydalanuvchi' }}</h2>
                <p>{{ $user->email }}</p>
                <span class="profile-role">
                    @if ($user->role === 'super_admin') Bosh Admin
                    @elseif ($user->role === 'admin') Admin
                    @elseif ($user->role === 'chevar') Chevar
                    @elseif ($user->role === 'ega') Ega
                    @else Mijoz
                    @endif
                </span>
            </div>

            <div class="profile-card">
                <div class="profile-card__head">
                    <h2>Ma’lumotlarni tahrirlash</h2>
                    <a href="{{ route('admin.dashboard') }}" class="card__link">← Orqaga</a>
                </div>

                <form action="{{ route('admin.profile.update') }}" method="POST" class="profile-card__body">
                    @csrf
                    @method('PUT')

                    <div class="profile-field">
                        <label class="profile-label">To‘liq ism</label>
                        <input type="text" name="toliq_ism" class="profile-input"
                               value="{{ old('toliq_ism', $user->toliq_ism) }}" required>
                    </div>

                    <div class="profile-field">
                        <label class="profile-label">Email</label>
                        <input type="email" name="email" class="profile-input"
                               value="{{ old('email', $user->email) }}" required>
                    </div>

                    <hr class="profile-divider">

                    <div class="profile-field">
                        <label class="profile-label">Yangi parol</label>
                        <input type="password" name="password" class="profile-input"
                               placeholder="Bo‘sh qoldiring — o‘zgarmaydi" autocomplete="new-password">
                        <p class="profile-hint">Kamida 8 belgidan iborat bo‘lsin</p>
                    </div>

                    <div class="profile-field">
                        <label class="profile-label">Parolni tasdiqlash</label>
                        <input type="password" name="password_confirmation" class="profile-input"
                               placeholder="Yangi parolni qayta yozing" autocomplete="new-password">
                    </div>

                    <div class="profile-actions">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn--ghost">Bekor qilish</a>
                        <button type="submit" class="btn btn--primary">Saqlash</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</x-layouts.sidebar>