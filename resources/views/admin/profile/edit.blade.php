<x-layouts.sidebar title="Profil">

    {{-- CSS ni layoutga qo‘shmagan bo‘lsangiz --}}
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}?v=1">

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

        {{-- Profil hero --}}
        <div class="profile-hero">
            <div class="profile-avatar">
                {{ mb_strtoupper(mb_substr($user->toliq_ism ?? 'A', 0, 1)) }}
            </div>
            <div class="profile-hero__info">
                <h2 class="profile-hero__name">{{ $user->toliq_ism ?? 'Foydalanuvchi' }}</h2>
                <p class="profile-hero__email">{{ $user->email }}</p>
                <span class="profile-role">
                    @if ($user->role === 'super_admin') Bosh Admin
                    @elseif ($user->role === 'admin') Admin
                    @elseif ($user->role === 'chevar') Chevar
                    @elseif ($user->role === 'ega') Ega
                    @else Mijoz
                    @endif
                </span>
            </div>
        </div>

        {{-- Forma kartasi --}}
        <div class="profile-form-card">
            <div class="card__head">
                <h2>Ma’lumotlarni tahrirlash</h2>
                <a href="{{ route('admin.dashboard') }}" class="card__link">← Orqaga</a>
            </div>

            <form action="{{ route('admin.profile.update') }}" method="POST" class="profile-form-body">
                @csrf
                @method('PUT')

                <p class="profile-section-title">Asosiy ma’lumotlar</p>

                <div class="profile-form-grid">
                    <div class="form-field">
                        <label>To‘liq ism</label>
                        <input type="text"
                               name="toliq_ism"
                               value="{{ old('toliq_ism', $user->toliq_ism) }}"
                               required
                               autocomplete="name"
                               placeholder="Ism Familiya">
                    </div>
                    <div class="form-field">
                        <label>Email</label>
                        <input type="email"
                               name="email"
                               value="{{ old('email', $user->email) }}"
                               required
                               autocomplete="email"
                               placeholder="email@example.com">
                    </div>
                </div>

                <p class="profile-section-title" style="margin-top:28px;">Parolni o‘zgartirish</p>

                <div class="profile-form-grid">
                    <div class="form-field">
                        <label>Yangi parol</label>
                        <input type="password"
                               name="password"
                               autocomplete="new-password"
                               placeholder="Bo‘sh qoldiring — o‘zgarmaydi">
                        <p class="profile-hint">Kamida 8 belgi, xavfsiz parol tavsiya etiladi</p>
                    </div>
                    <div class="form-field">
                        <label>Parolni tasdiqlash</label>
                        <input type="password"
                               name="password_confirmation"
                               autocomplete="new-password"
                               placeholder="Yangi parolni qayta yozing">
                    </div>
                </div>

                <div class="profile-form-actions">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn--ghost">Bekor qilish</a>
                    <button type="submit" class="btn btn--primary">
                        Saqlash
                    </button>
                </div>
            </form>
        </div>

    </div>

</x-layouts.sidebar>