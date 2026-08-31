<x-layouts.sidebar title="Xodimlar">

    <div class="content">

        @if (session('success'))
            <div class="alert alert--success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert--error">{{ session('error') }}</div>
        @endif


        <form method="GET" action="{{ route('admin.xodimlar.index') }}"
            style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;align-items:end;">
            <div class="form-field" style="margin:0;min-width:160px;">
                <label style="font-size:12px;">Xodim</label>
                <select name="chevar_id" data-searchable>
                    <option value="">Barchasi</option>
                    @foreach ($chevarlar as $ch)
                        <option value="{{ $ch->id }}" @selected(($filterChevar ?? '') == $ch->id)>{{ $ch->toliq_ism }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-field" style="margin:0;min-width:160px;">
                <label style="font-size:12px;">Mahsulot (ish/brak)</label>
                <select name="product_id" data-searchable>
                    <option value="">Barchasi</option>
                    @foreach ($products as $pr)
                        <option value="{{ $pr->id }}" @selected(($filterProduct ?? '') == $pr->id)>{{ $pr->nomi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-field" style="margin:0;min-width:180px;">
                <label style="font-size:12px;">Qidiruv</label>
                <input type="search" name="q" value="{{ $q ?? '' }}" placeholder="ism, izoh, sabab...">
            </div>
            <button type="submit" class="btn btn--primary">Filter</button>
            <a href="{{ route('admin.xodimlar.index') }}" class="btn btn--ghost">Tozalash</a>
        </form>

        <nav class="page-toc">
            <a href="#acc-balans">Balans</a>
            <a href="#acc-ish">Ishlar</a>
            <a href="#acc-brak">Braklar</a>
            <a href="#acc-tolov">To'lovlar</a>
        </nav>

        <div style="display:flex; justify-content:flex-end; gap:10px; margin-bottom:16px; flex-wrap:wrap;">
            <button type="button" class="btn btn--ghost" onclick="openModal('chevar-modal')">+ Xodim (chevar)</button>
            <button type="button" class="btn btn--ghost" onclick="openModal('ish-modal')">+ Ish yozish</button>
            <button type="button" class="btn btn--ghost" onclick="openModal('brak-xodim-modal')">+ Brak</button>
            <button type="button" class="btn btn--primary" onclick="openModal('tolov-modal')">+ To'lov</button>
        </div>

        <div class="acc is-open" id="acc-balans">
            <button type="button" class="acc__head" onclick="toggleAcc(this)">
                <span class="acc__head-left">
                    <span class="acc__icon">◎</span>
                    <span>Xodimlar balansi</span>
                    <span class="acc__meta">{{ $chevarlar->count() }} ta</span>
                </span>
                <span class="acc__chevron">▾</span>
            </button>
            <div class="acc__body">
                <div class="acc__scroll" style="overflow-x:auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ism</th>
                                <th>Tel</th>
                                <th>Tikgan dona</th>
                                <th>Brak dona</th>
                                <th>Pul Donaga</th>
                                <th>Jami ish puli</th>
                                <th>Brak jarima</th>
                                <th>To'langan</th>
                                <th>Qolgan balans</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($chevarlar as $ch)
                                <tr>
                                    <td>
                                        <p class="rank-item__name" style="margin:0;">{{ $ch->toliq_ism }}</p>
                                    </td>
                                    <td>{{ $ch->tel_nomer }}</td>
                                    <td class="mono">{{ $ch->jami_dona }}</td>
                                    <td class="mono">{{ $ch->brak_dona }}</td>
                                    <td class="mono">{{ number_format($ch->pul_dona, 0, '.', ' ') }}</td>
                                    <td class="mono">{{ number_format($ch->jami_ish_pul, 0, '.', ' ') }}</td>
                                    <td class="mono">{{ number_format($ch->jami_jarima, 0, '.', ' ') }}</td>
                                    <td class="mono">{{ number_format($ch->jami_tolov, 0, '.', ' ') }}</td>
                                    <td class="mono"><strong>{{ number_format($ch->balans, 0, '.', ' ') }}
                                            so'm</strong></td>
                                    <td>
                                        <button type="button" class="card__link"
                                            style="background:none;border:none;cursor:pointer;font:inherit;padding:0;margin-right:8px;"
                                            onclick="openEditChevar({{ $ch->id }}, '{{ addslashes($ch->toliq_ism) }}', '{{ addslashes($ch->tel_nomer) }}', '{{ addslashes($ch->email) }}')">Tahrirlash</button>
                                        <form action="{{ route('admin.xodimlar.destroy', $ch) }}" method="POST"
                                            style="display:inline;" onsubmit="return confirm('O\'chirilsinmi?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="card__link"
                                                style="background:none;border:none;cursor:pointer;color:var(--rust);font:inherit;padding:0;">O'chirish</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" style="text-align:center;color:var(--ink-faint);padding:30px 0;">
                                        Hali chevar yo'q. «+ Xodim» bosing.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="rank-item__sub" style="padding:12px 16px;">
                    <strong>Balans</strong> = ish puli − brak jarima − to'langan.
                    Masalan: 500 000 ish − 0 jarima − 400 000 to'lov = <strong>100 000</strong> qoldiq.
                </p>
            </div>

        </div>

        <div class="acc is-open" id="acc-ish">
            <button type="button" class="acc__head" onclick="toggleAcc(this)">
                <span class="acc__head-left">
                    <span class="acc__icon">✎</span>
                    <span>Ishlar tarixi</span>
                    <span class="acc__meta">{{ $ishlar->count() }} ta</span>
                </span>
                <span class="acc__chevron">▾</span>
            </button>
            <div class="acc__body">
                <div class="acc__scroll" style="overflow-x:auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sana</th>
                                <th>Chevar</th>
                                <th>Mahsulot</th>
                                <th>Miqdor</th>
                                <th>1 dona</th>
                                <th>Jami</th>
                                <th>Izoh</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ishlar as $i)
                                <tr>
                                    <td class="mono">
                                        {{ \Illuminate\Support\Carbon::parse($i->sana)->format('d.m.Y') }}
                                    </td>
                                    <td>{{ $i->chevar->toliq_ism ?? '—' }}</td>
                                    <td>{{ $i->product->nomi ?? '—' }} - {{$i->product->rangi}}</td>
                                    <td class="mono">{{ $i->miqdori }}</td>
                                    <td class="mono">{{ number_format($i->pul_dona, 0, '.', ' ') }}</td>
                                    <td class="mono">{{ number_format($i->jami_pul, 0, '.', ' ') }}</td>
                                    <td>{{ $i->izoh ?? '—' }}</td>
                                    <td>
                                        <button type="button" class="card__link"
                                            style="background:none;border:none;cursor:pointer;font:inherit;padding:0;margin-right:6px;"
                                            onclick='openEditIsh(@json($i->id), @json($i->chevar_id), @json($i->product_id), @json($i->miqdori), @json((float) $i->pul_dona), @json($i->izoh), @json($i->sana))'>✎</button>
                                        <form action="{{ route('admin.chevar-ishlari.destroy', $i) }}" method="POST"
                                            style="display:inline;" onsubmit="return confirm('O\'chirilsinmi?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="card__link"
                                                style="background:none;border:none;cursor:pointer;color:var(--rust);font:inherit;padding:0;">×</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8"
                                        style="text-align:center;color:var(--ink-faint);padding:30px 0;">
                                        Ish yo'q.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>



        <div class="acc" id="acc-brak">
            <button type="button" class="acc__head" onclick="toggleAcc(this)">
                <span class="acc__head-left">
                    <span class="acc__icon">!</span>
                    <span>Braklar</span>
                    <span class="acc__meta">{{ $braklar->count() }} ta</span>
                </span>
                <span class="acc__chevron">▾</span>
            </button>
            <div class="acc__body">
                <div class="acc__scroll" style="overflow-x:auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sana</th>
                                <th>Chevar</th>
                                <th>Mahsulot</th>
                                <th>Miqdor</th>
                                <th>Material zarar</th>
                                <th>Chevar jarima</th>
                                <th>Sabab</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($braklar as $b)
                                <tr>
                                    <td class="mono">
                                        {{ \Illuminate\Support\Carbon::parse($b->sana)->format('d.m.Y') }}
                                    </td>
                                    <td>{{ $b->chevar->toliq_ism ?? '—' }}</td>
                                    <td>{{ $b->product->nomi ?? '—' }}</td>
                                    <td class="mono">{{ $b->miqdori }}</td>
                                    <td class="mono">{{ number_format($b->summa, 0, '.', ' ') }}</td>
                                    <td class="mono">−{{ number_format($b->chevar_jarima, 0, '.', ' ') }}</td>
                                    <td>{{ $b->sabab ?? '—' }}</td>
                                    <td>
                                        <button type="button" class="card__link"
                                            style="background:none;border:none;cursor:pointer;font:inherit;padding:0;margin-right:6px;"
                                            onclick='openEditBrak(@json($b->id), @json($b->chevar_id), @json($b->product_id), @json($b->miqdori), @json((float) $b->chevar_jarima / max(1, (int) $b->miqdori)), @json($b->sabab), @json($b->sana))'>✎</button>
                                        <form action="{{ route('admin.braklar.destroy', $b) }}" method="POST"
                                            style="display:inline;" onsubmit="return confirm('O\'chirilsinmi?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="card__link"
                                                style="background:none;border:none;cursor:pointer;color:var(--rust);font:inherit;padding:0;">×</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8"
                                        style="text-align:center;color:var(--ink-faint);padding:30px 0;">
                                        Brak yo'q.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>


        <div class="acc" id="acc-tolov">
            <button type="button" class="acc__head" onclick="toggleAcc(this)">
                <span class="acc__head-left">
                    <span class="acc__icon">$</span>
                    <span>To'lovlar</span>
                    <span class="acc__meta">{{ $tolovlar->count() }} ta</span>
                </span>
                <span class="acc__chevron">▾</span>
            </button>
            <div class="acc__body">
                <div class="acc__scroll" style="overflow-x:auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sana</th>
                                <th>Chevar</th>
                                <th>Summa</th>
                                <th>Izoh</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tolovlar as $t)
                                <tr>
                                    <td class="mono">
                                        {{ \Illuminate\Support\Carbon::parse($t->sana)->format('d.m.Y') }}
                                    </td>
                                    <td>{{ $t->chevar->toliq_ism ?? '—' }}</td>
                                    <td class="mono">{{ number_format($t->summa, 0, '.', ' ') }} so'm</td>
                                    <td>{{ $t->izoh ?? '—' }}</td>
                                    <td>
                                        <button type="button" class="card__link"
                                            style="background:none;border:none;cursor:pointer;font:inherit;padding:0;margin-right:6px;"
                                            onclick='openEditTolov(@json($t->id), @json($t->chevar_id), @json((float) $t->summa), @json($t->sana), @json($t->izoh))'>✎</button>
                                        <form action="{{ route('admin.chevar-tolovlar.destroy', $t) }}"
                                            method="POST" style="display:inline;"
                                            onsubmit="return confirm('O\'chirilsinmi?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="card__link"
                                                style="background:none;border:none;cursor:pointer;color:var(--rust);font:inherit;padding:0;">×</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        style="text-align:center;color:var(--ink-faint);padding:30px 0;">
                                        To'lov yo'q.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="chevar-modal">
        <div class="modal" style="max-width:420px;">
            <div class="modal__head">
                <h2>Yangi xodim (chevar)</h2>
                <button type="button" class="modal__close" onclick="closeModal('chevar-modal')">&times;</button>
            </div>
            <form action="{{ route('admin.xodimlar.store') }}" method="POST">
                @csrf
                <div class="modal__body">
                    <div class="form-field"><label>To'liq ism</label>
                        <input type="text" name="toliq_ism" required placeholder="Gulnora Karimova">
                    </div>
                    <div class="form-field" style="margin-top:12px;"><label>Telefon</label>
                        <input type="text" name="tel_nomer" required placeholder="+99890...">
                    </div>
                    <div class="form-field" style="margin-top:12px;"><label>Email (ixtiyoriy)</label>
                        <input type="email" name="email">
                    </div>
                    <div class="form-field" style="margin-top:12px;"><label>Parol (default: chevar123)</label>
                        <input type="text" name="password" placeholder="chevar123">
                    </div>
                </div>
                <div class="modal__foot">
                    <button type="button" class="btn btn--ghost" onclick="closeModal('chevar-modal')">Bekor</button>
                    <button type="submit" class="btn btn--primary">Saqlash</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="ish-modal">
        <div class="modal" style="max-width:460px;">
            <div class="modal__head">
                <h2>Ish yozish</h2>
                <button type="button" class="modal__close" onclick="closeModal('ish-modal')">&times;</button>
            </div>
            <form action="{{ route('admin.chevar-ishlari.store') }}" method="POST">
                @csrf
                <div class="modal__body">
                    <p class="rank-item__sub" style="margin-bottom:12px;">Bir mahsulotni bir nechta chevar bo'lib
                        tikishi mumkin.</p>
                    <div class="form-field"><label>Chevar</label>
                        <select name="chevar_id" data-searchable required>
                            <option value="" disabled selected>Tanlang</option>
                            @foreach ($chevarlar as $ch)
                                <option value="{{ $ch->id }}">{{ $ch->toliq_ism }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field" style="margin-top:12px;"><label>Mahsulot</label>
                        <select name="product_id" data-searchable required>
                            <option value="" disabled selected>Tanlang</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}">{{ $p->nomi }} - {{$p->rangi}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-grid" style="grid-template-columns:1fr 1fr;margin-top:12px;">
                        <div class="form-field"><label>Miqdori</label>
                            <input type="number" name="miqdori" min="1" required>
                        </div>
                        <div class="form-field"><label>1 dona puli</label>
                            <input type="number" name="pul_dona" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="form-grid" style="grid-template-columns:1fr 1fr;margin-top:12px;">
                        <div class="form-field"><label>Izoh</label>
                            <input type="text" name="izoh" placeholder="yeng / asosi">
                        </div>
                        <div class="form-field"><label>Sana</label>
                            <input type="date" name="sana" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                <div class="modal__foot">
                    <button type="button" class="btn btn--ghost" onclick="closeModal('ish-modal')">Bekor</button>
                    <button type="submit" class="btn btn--primary">Saqlash</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="tolov-modal">
        <div class="modal" style="max-width:420px;">
            <div class="modal__head">
                <h2>To'lov berish</h2>
                <button type="button" class="modal__close" onclick="closeModal('tolov-modal')">&times;</button>
            </div>
            <form action="{{ route('admin.chevar-tolovlar.store') }}" method="POST">
                @csrf
                <div class="modal__body">
                    <div class="form-field"><label>Chevar</label>
                        <select name="chevar_id" data-searchable required>
                            <option value="" disabled selected>Tanlang</option>
                            @foreach ($chevarlar as $ch)
                                <option value="{{ $ch->id }}">{{ $ch->toliq_ism }} —
                                    {{ number_format($ch->balans, 0, '.', ' ') }} so'm</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field" style="margin-top:12px;"><label>Summa</label>
                        <input type="number" name="summa" min="0.01" step="0.01" required
                            placeholder="400000">
                    </div>
                    <div class="form-field" style="margin-top:12px;"><label>Sana</label>
                        <input type="date" name="sana" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-field" style="margin-top:12px;"><label>Izoh</label>
                        <input type="text" name="izoh" placeholder="Oylik">
                    </div>
                </div>
                <div class="modal__foot">
                    <button type="button" class="btn btn--ghost" onclick="closeModal('tolov-modal')">Bekor</button>
                    <button type="submit" class="btn btn--primary">To'lash</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="brak-xodim-modal">
        <div class="modal" style="max-width:420px;">
            <div class="modal__head">
                <h2>Brak (chevar)</h2>
                <button type="button" class="modal__close" onclick="closeModal('brak-xodim-modal')">&times;</button>
            </div>
            <form action="{{ route('admin.braklar.store') }}" method="POST">
                @csrf
                <div class="modal__body">
                    <p class="rank-item__sub" style="margin-bottom:12px;">Ombordan − va chevar balansidan jarima.</p>
                    <div class="form-field"><label>Chevar</label>
                        <select name="chevar_id" data-searchable required>
                            <option value="" disabled selected>Tanlang</option>
                            @foreach ($chevarlar as $ch)
                                <option value="{{ $ch->id }}">{{ $ch->toliq_ism }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field" style="margin-top:12px;"><label>Mahsulot</label>
                        <select name="product_id" data-searchable required>
                            <option value="" disabled selected>Tanlang</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}">{{ $p->nomi }} - {{$p->rangi}} ({{ $p->soni }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-grid" style="grid-template-columns:1fr 1fr;margin-top:12px;">
                        <div class="form-field"><label>Brak miqdori</label>
                            <input type="number" name="miqdori" min="1" required>
                        </div>
                        <div class="form-field"><label>1 dona jarima</label>
                            <input type="number" name="pul_dona" min="0" step="0.01"
                                placeholder="stavka">
                        </div>
                    </div>
                    <div class="form-field" style="margin-top:12px;"><label>Sabab</label>
                        <input type="text" name="sabab">
                    </div>
                    <div class="form-field" style="margin-top:12px;"><label>Sana</label>
                        <input type="date" name="sana" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal__foot">
                    <button type="button" class="btn btn--ghost"
                        onclick="closeModal('brak-xodim-modal')">Bekor</button>
                    <button type="submit" class="btn btn--primary">Saqlash</button>
                </div>
            </form>
        </div>
    </div>


    <div class="modal-overlay" id="edit-chevar-modal">
        <div class="modal" style="max-width:420px;">
            <div class="modal__head">
                <h2>Xodim tahrirlash</h2>
                <button type="button" class="modal__close"
                    onclick="closeModal('edit-chevar-modal')">&times;</button>
            </div>
            <form id="edit-chevar-form" method="POST">
                @csrf @method('PUT')
                <div class="modal__body">
                    <div class="form-field"><label>Ism</label><input type="text" name="toliq_ism" id="ec-ism"
                            required></div>
                    <div class="form-field" style="margin-top:12px;"><label>Tel</label><input type="text"
                            name="tel_nomer" id="ec-tel" required></div>
                    <div class="form-field" style="margin-top:12px;"><label>Email</label><input type="email"
                            name="email" id="ec-email"></div>
                    <div class="form-field" style="margin-top:12px;"><label>Yangi parol (ixtiyoriy)</label><input
                            type="text" name="password"></div>
                </div>
                <div class="modal__foot">
                    <button type="button" class="btn btn--ghost"
                        onclick="closeModal('edit-chevar-modal')">Bekor</button>
                    <button type="submit" class="btn btn--primary">Saqlash</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="edit-ish-modal">
        <div class="modal" style="max-width:460px;">
            <div class="modal__head">
                <h2>Ish tahrirlash</h2>
                <button type="button" class="modal__close" onclick="closeModal('edit-ish-modal')">&times;</button>
            </div>
            <form id="edit-ish-form" method="POST">
                @csrf @method('PUT')
                <div class="modal__body">
                    <div class="form-field"><label>Chevar</label>
                        <select name="chevar_id" data-searchable id="ei-chevar" required>
                            @foreach ($chevarlar as $ch)
                                <option value="{{ $ch->id }}">{{ $ch->toliq_ism }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field" style="margin-top:12px;"><label>Mahsulot</label>
                        <select name="product_id" data-searchable id="ei-product" required>
                            @foreach ($products as $pr)
                                <option value="{{ $pr->id }}">{{ $pr->nomi }} - {{$pr->rangi}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-grid" style="grid-template-columns:1fr 1fr;margin-top:12px;">
                        <div class="form-field"><label>Miqdor</label><input type="number" name="miqdori"
                                id="ei-miq" min="1" required></div>
                        <div class="form-field"><label>1 dona puli</label><input type="number" name="pul_dona"
                                id="ei-pul" min="0" step="0.01" required></div>
                    </div>
                    <div class="form-grid" style="grid-template-columns:1fr 1fr;margin-top:12px;">
                        <div class="form-field"><label>Izoh</label><input type="text" name="izoh"
                                id="ei-izoh"></div>
                        <div class="form-field"><label>Sana</label><input type="date" name="sana"
                                id="ei-sana"></div>
                    </div>
                </div>
                <div class="modal__foot">
                    <button type="button" class="btn btn--ghost"
                        onclick="closeModal('edit-ish-modal')">Bekor</button>
                    <button type="submit" class="btn btn--primary">Saqlash</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="edit-tolov-modal">
        <div class="modal" style="max-width:420px;">
            <div class="modal__head">
                <h2>To'lov tahrirlash</h2>
                <button type="button" class="modal__close" onclick="closeModal('edit-tolov-modal')">&times;</button>
            </div>
            <form id="edit-tolov-form" method="POST">
                @csrf @method('PUT')
                <div class="modal__body">
                    <div class="form-field"><label>Chevar</label>
                        <select name="chevar_id" data-searchable id="et-chevar" required>
                            @foreach ($chevarlar as $ch)
                                <option value="{{ $ch->id }}">{{ $ch->toliq_ism }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field" style="margin-top:12px;"><label>Summa</label><input type="number"
                            name="summa" id="et-summa" min="0.01" step="0.01" required></div>
                    <div class="form-field" style="margin-top:12px;"><label>Sana</label><input type="date"
                            name="sana" id="et-sana"></div>
                    <div class="form-field" style="margin-top:12px;"><label>Izoh</label><input type="text"
                            name="izoh" id="et-izoh"></div>
                </div>
                <div class="modal__foot">
                    <button type="button" class="btn btn--ghost"
                        onclick="closeModal('edit-tolov-modal')">Bekor</button>
                    <button type="submit" class="btn btn--primary">Saqlash</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="edit-brak-modal">
        <div class="modal" style="max-width:420px;">
            <div class="modal__head">
                <h2>Brak tahrirlash</h2>
                <button type="button" class="modal__close" onclick="closeModal('edit-brak-modal')">&times;</button>
            </div>
            <form id="edit-brak-form" method="POST">
                @csrf @method('PUT')
                <div class="modal__body">
                    <div class="form-field"><label>Chevar</label>
                        <select name="chevar_id" data-searchable id="eb-chevar">
                            <option value="">—</option>
                            @foreach ($chevarlar as $ch)
                                <option value="{{ $ch->id }}">{{ $ch->toliq_ism }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field" style="margin-top:12px;"><label>Mahsulot</label>
                        <select name="product_id" data-searchable id="eb-product" required>
                            @foreach ($products as $pr)
                                <option value="{{ $pr->id }}">{{ $pr->nomi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-grid" style="grid-template-columns:1fr 1fr;margin-top:12px;">
                        <div class="form-field"><label>Miqdor</label><input type="number" name="miqdori"
                                id="eb-miq" min="1" required></div>
                        <div class="form-field"><label>1 dona jarima</label><input type="number" name="pul_dona"
                                id="eb-pul" min="0" step="0.01"></div>
                    </div>
                    <div class="form-field" style="margin-top:12px;"><label>Sabab</label><input type="text"
                            name="sabab" id="eb-sabab"></div>
                    <div class="form-field" style="margin-top:12px;"><label>Sana</label><input type="date"
                            name="sana" id="eb-sana"></div>
                </div>
                <div class="modal__foot">
                    <button type="button" class="btn btn--ghost"
                        onclick="closeModal('edit-brak-modal')">Bekor</button>
                    <button type="submit" class="btn btn--primary">Saqlash</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        /* ---- Accordion ---- */
        function toggleAcc(btn) {
            const acc = btn.closest('.acc');
            acc.classList.toggle('is-open');
        }
        document.querySelectorAll('.page-toc a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                const id = a.getAttribute('href').slice(1);
                const el = document.getElementById(id);
                if (!el) return;
                e.preventDefault();
                el.classList.add('is-open');
                el.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });

        /* ---- Search Select: native <select data-searchable> -> searchable ---- */
        function enhanceSelects(root) {
            (root || document).querySelectorAll('select[data-searchable]').forEach(sel => {
                if (sel.dataset.ssDone) return;
                sel.dataset.ssDone = '1';
                sel.style.display = 'none';

                const wrap = document.createElement('div');
                wrap.className = 'search-select';
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'ss-input';
                input.placeholder = sel.dataset.placeholder || 'Yozib qidiring...';
                input.autocomplete = 'off';
                const list = document.createElement('div');
                list.className = 'ss-list';

                const options = [...sel.options].map(o => ({
                    value: o.value,
                    label: o.textContent.trim(),
                    disabled: o.disabled,
                    selected: o.selected
                }));

                function render(q) {
                    const qq = (q || '').toLowerCase().trim();
                    list.innerHTML = '';
                    let n = 0;
                    options.forEach(o => {
                        if (!o.value && o.disabled) return; // skip placeholder empty disabled
                        if (qq && !o.label.toLowerCase().includes(qq)) return;
                        const div = document.createElement('div');
                        div.className = 'ss-item' + (o.value === sel.value ? ' is-active' : '');
                        div.textContent = o.label;
                        div.dataset.value = o.value;
                        div.addEventListener('mousedown', e => {
                            e.preventDefault();
                            sel.value = o.value;
                            input.value = o.label;
                            list.classList.remove('is-open');
                            sel.dispatchEvent(new Event('change', {
                                bubbles: true
                            }));
                        });
                        list.appendChild(div);
                        n++;
                    });
                    if (!n) {
                        const empty = document.createElement('div');
                        empty.className = 'ss-empty';
                        empty.textContent = 'Topilmadi';
                        list.appendChild(empty);
                    }
                }

                const selected = options.find(o => o.value === sel.value && o.value);
                if (selected) input.value = selected.label;

                input.addEventListener('focus', () => {
                    render(input.value);
                    list.classList.add('is-open');
                });
                input.addEventListener('input', () => {
                    render(input.value);
                    list.classList.add('is-open');
                });
                input.addEventListener('keydown', e => {
                    if (e.key === 'Escape') list.classList.remove('is-open');
                });

                wrap.appendChild(input);
                wrap.appendChild(list);
                sel.parentNode.insertBefore(wrap, sel.nextSibling);
            });
        }
        document.addEventListener('click', e => {
            if (!e.target.closest('.search-select')) {
                document.querySelectorAll('.ss-list.is-open').forEach(l => l.classList.remove('is-open'));
            }
        });
        document.addEventListener('DOMContentLoaded', () => enhanceSelects(document));

        /* Modal open/close */
        function openModal(id) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.add('is-open');
                enhanceSelects(el);
            }
        }

        function closeModal(id) {
            document.getElementById(id)?.classList.remove('is-open');
        }
    </script>
</x-layouts.sidebar>
