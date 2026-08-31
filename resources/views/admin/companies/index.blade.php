<x-layouts.sidebar title="Companiyalar">

    <div class="content">

        @if (session('success'))
            <div class="alert alert--success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert--error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert--error">
                @foreach ($errors->all() as $xato)
                    <div>{{ $xato }}</div>
                @endforeach
            </div>
        @endif

        <div class="company-head">
            <div>
                <p class="company-head__sub">Har bir companiyaning kirish muddati, holati va loginlarini shu yerdan boshqaring.</p>
            </div>
            <button type="button" class="btn btn--primary" onclick="openModal('company-modal')">+ Companiya</button>
        </div>

        <div class="company-grid">
            @forelse ($companies as $company)
                @php
                    $tugaganmi = $company->kirish_muddati && $company->kirish_muddati->isPast();
                    $bloklangan = ! $company->faol || $tugaganmi;
                    $qolganKun = $company->kirish_muddati ? (int) now()->startOfDay()->diffInDays($company->kirish_muddati, false) : null;
                    $cardClass = $bloklangan ? 'company-card--blocked' : ($qolganKun !== null && $qolganKun <= 5 ? 'company-card--warn' : 'company-card--ok');
                @endphp
                <div class="company-card {{ $cardClass }}">
                    <div class="company-card__head">
                        <span class="company-card__avatar">{{ mb_strtoupper(mb_substr($company->nomi, 0, 1)) }}</span>
                        <div>
                            <div class="company-card__name">{{ $company->nomi }}</div>
                            @if ($company->tel_nomer)
                                <div class="company-card__phone">{{ $company->tel_nomer }}</div>
                            @endif
                        </div>
                    </div>

                    @if (! $company->faol)
                        <span class="company-badge company-badge--danger">Bloklangan</span>
                    @elseif ($tugaganmi)
                        <span class="company-badge company-badge--danger">Muddati tugagan</span>
                    @elseif ($company->kirish_muddati)
                        <span class="company-badge {{ $qolganKun <= 5 ? 'company-badge--warn' : 'company-badge--ok' }}">
                            {{ $qolganKun }} kun qoldi
                        </span>
                    @else
                        <span class="company-badge company-badge--ok">Muddatsiz</span>
                    @endif

                    @if ($company->izoh)
                        <div class="company-card__note">{{ $company->izoh }}</div>
                    @endif

                    {{-- Ulangan loginlar --}}
                    <div class="company-card__users">
                        @forelse ($company->users as $u)
                            <div class="company-user-row">
                                <span class="company-user-row__email" title="{{ $u->toliq_ism }} · {{ $u->role }}">{{ $u->email }}</span>
                                <form method="POST" action="{{ route('admin.companies.users.destroy', [$company, $u]) }}"
                                      onsubmit="return confirm('«{{ $u->email }}» login o\'chirilsinmi?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="company-user-row__remove" title="O'chirish">&times;</button>
                                </form>
                            </div>
                        @empty
                            <div class="company-card__note">Hali login biriktirilmagan.</div>
                        @endforelse
                    </div>

                    <div class="company-card__actions">
                        <a href="{{ route('admin.companies.select', $company) }}" class="btn btn--primary">Kirish</a>
                        <button type="button" class="btn btn--ghost"
                            onclick="openUserModal({{ $company->id }}, {{ Js::from($company->nomi) }})">Login biriktirish</button>
                        <button type="button" class="btn btn--ghost"
                            onclick="openExtendModal({{ $company->id }}, {{ Js::from($company->nomi) }})">Muddat</button>
                        <button type="button" class="btn btn--ghost"
                            onclick="document.getElementById('block-form-{{ $company->id }}').submit()">
                            {{ $company->faol ? 'Bloklash' : 'Yechish' }}
                        </button>
                        <form id="block-form-{{ $company->id }}" method="POST"
                            action="{{ route('admin.companies.toggleBlock', $company) }}" style="display:none;">
                            @csrf
                            @method('PUT')
                        </form>
                    </div>
                </div>
            @empty
                <div class="company-empty">Hali companiya qo'shilmagan.</div>
            @endforelse
        </div>

        {{-- Yangi companiya qo'shish --}}
        <div id="company-modal" class="modal-overlay">
            <div class="modal">
                <div class="modal__head">
                    <h2>Yangi companiya</h2>
                    <button type="button" class="modal__close" onclick="closeModal('company-modal')">&times;</button>
                </div>
                <form method="POST" action="{{ route('admin.companies.store') }}">
                    @csrf
                    <div class="modal__body">
                        <div class="form-grid">
                            <div class="form-field">
                                <label for="company-nomi">Nomi</label>
                                <input type="text" id="company-nomi" name="nomi" required>
                            </div>
                            <div class="form-field">
                                <label for="company-tel">Telefon</label>
                                <input type="text" id="company-tel" name="tel_nomer">
                            </div>
                            <div class="form-field">
                                <label for="company-muddat">Boshlang'ich muddat (kun)</label>
                                <input type="number" id="company-muddat" name="muddat_kun" min="1" value="30">
                            </div>
                            <div class="form-field" style="grid-column: 1 / -1;">
                                <label for="company-izoh">Izoh</label>
                                <textarea id="company-izoh" name="izoh" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal__foot">
                        <button type="button" class="btn btn--ghost" onclick="closeModal('company-modal')">Bekor qilish</button>
                        <button type="submit" class="btn btn--primary">Saqlash</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Muddatni uzaytirish --}}
        <div id="extend-modal" class="modal-overlay">
            <div class="modal">
                <div class="modal__head">
                    <h2 id="extend-modal-title">Muddatni uzaytirish</h2>
                    <button type="button" class="modal__close" onclick="closeModal('extend-modal')">&times;</button>
                </div>
                <form method="POST" id="extend-form">
                    @csrf
                    @method('PUT')
                    <div class="modal__body">
                        <div class="form-field">
                            <label for="extend-kun">Necha kunga uzaytirish</label>
                            <input type="number" id="extend-kun" name="kun" min="1" value="30" required>
                        </div>
                    </div>
                    <div class="modal__foot">
                        <button type="button" class="btn btn--ghost" onclick="closeModal('extend-modal')">Bekor qilish</button>
                        <button type="submit" class="btn btn--primary">Uzaytirish</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Companiyaga login biriktirish --}}
        <div id="user-modal" class="modal-overlay">
            <div class="modal">
                <div class="modal__head">
                    <h2 id="user-modal-title">Login biriktirish</h2>
                    <button type="button" class="modal__close" onclick="closeModal('user-modal')">&times;</button>
                </div>
                <form method="POST" id="user-form">
                    @csrf
                    <div class="modal__body">
                        <div class="form-grid">
                            <div class="form-field" style="grid-column: 1 / -1;">
                                <label for="user-ism">To'liq ism</label>
                                <input type="text" id="user-ism" name="toliq_ism" value="{{ old('toliq_ism') }}" required>
                            </div>
                            <div class="form-field">
                                <label for="user-tel">Telefon</label>
                                <input type="text" id="user-tel" name="tel_nomer" value="{{ old('tel_nomer') }}" required placeholder="901234567">
                            </div>
                            <div class="form-field">
                                <label for="user-role">Rol</label>
                                <select id="user-role" name="role" required>
                                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                                    <option value="chevar" @selected(old('role') === 'chevar')>Chevar</option>
                                    <option value="client" @selected(old('role') === 'client')>Mijoz</option>
                                </select>
                            </div>
                            <div class="form-field" style="grid-column: 1 / -1;">
                                <label for="user-email">Email</label>
                                <input type="email" id="user-email" name="email" value="{{ old('email') }}" required placeholder="email@misol.uz">
                            </div>
                            <div class="form-field" style="grid-column: 1 / -1;">
                                <label for="user-password">Parol</label>
                                <input type="password" id="user-password" name="password" required minlength="6">
                            </div>
                        </div>
                    </div>
                    <div class="modal__foot">
                        <button type="button" class="btn btn--ghost" onclick="closeModal('user-modal')">Bekor qilish</button>
                        <button type="submit" class="btn btn--primary">Biriktirish</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Mustaqil modal funksiyalari — boshqa sahifalarga bog'liq emas.
        // Agar globalda allaqachon shu nomdagi funksiya bo'lsa, uni almashtirmaymiz.
        if (typeof window.openModal !== 'function') {
            window.openModal = function (id) {
                var el = document.getElementById(id);
                if (el) el.classList.add('is-open');
            };
        }
        if (typeof window.closeModal !== 'function') {
            window.closeModal = function (id) {
                var el = document.getElementById(id);
                if (el) el.classList.remove('is-open');
            };
        }

        // Overlay foniga bosilsa yopilsin
        document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) {
                    overlay.classList.remove('is-open');
                }
            });
        });

        // Escape tugmasi bilan yopish
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.is-open').forEach(function (el) {
                    el.classList.remove('is-open');
                });
            }
        });

        function openExtendModal(companyId, name) {
            document.getElementById('extend-modal-title').textContent = '«' + name + '» — muddatni uzaytirish';
            document.getElementById('extend-form').action = '/admin/companies/' + companyId + '/extend';
            openModal('extend-modal');
        }

        function openUserModal(companyId, name) {
            document.getElementById('user-modal-title').textContent = '«' + name + '» — login biriktirish';
            document.getElementById('user-form').action = '/admin/companies/' + companyId + '/users';
            openModal('user-modal');
        }
    </script>
</x-layouts.sidebar>