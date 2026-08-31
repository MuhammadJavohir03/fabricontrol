<x-layouts.sidebar title="Mahsulotlar">

    <div class="content">

        @if (session('success'))
            <div class="alert alert--success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert--error">{{ session('error') }}</div>
        @endif

        {{-- Tugmalar --}}
        <div style="display:flex; justify-content:flex-end; gap:10px; margin-bottom:16px; flex-wrap:wrap;">
            <button type="button" class="btn btn--ghost" onclick="openModal('brak-modal')">+ Brak</button>
            <button type="button" class="btn btn--ghost" onclick="openModal('yoqotish-modal')">+ Yo'qotish</button>
            <button type="button" class="btn btn--ghost" onclick="openModal('chiqim-modal')">+ Chiqim</button>
            <button type="button" class="btn btn--ghost" onclick="openXomashyoCreate()">+ Xomashyo</button>
            <button type="button" class="btn btn--primary" onclick="openModal('mahsulot-modal')">+ Mahsulot</button>
        </div>

        <nav class="page-toc" aria-label="Mundarija">
            <a href="#acc-mahsulot">Mahsulotlar</a>
            <a href="#acc-xomashyo">Xomashyolar</a>
            <a href="#acc-sotuv">Sotuvlar</a>
            <a href="#acc-zarar">Zararlar</a>
        </nav>

        {{-- Statistika --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-card__top"><span class="stat-card__label">Mahsulot turlari</span></div>
                <div class="stat-card__value">{{ $stats['jami_mahsulot'] }}</div>
            </div>
            <div class="stat-card stat-card--gold">
                <div class="stat-card__top"><span class="stat-card__label">Xomashyo qiymati</span></div>
                <div class="stat-card__value mono">{{ number_format($stats['xomashyo_qiymati'], 0, '.', ' ') }}</div>
            </div>
            <div class="stat-card stat-card--rust">
                <div class="stat-card__top"><span class="stat-card__label">Jami zarar</span></div>
                <div class="stat-card__value mono">{{ number_format($stats['jami_zarar'], 0, '.', ' ') }}</div>
            </div>
            <div class="stat-card stat-card--sage">
                <div class="stat-card__top">
                    <span class="stat-card__label">Sof foyda</span>
                    <span class="stat-card__trend {{ $stats['jami_foyda'] < 0 ? 'stat-card__trend--warn' : '' }}">
                        {{ $stats['jami_foyda'] < 0 ? 'Zarar' : 'Foyda' }}
                    </span>
                </div>
                <div class="stat-card__value mono">{{ number_format($stats['jami_foyda'], 0, '.', ' ') }}</div>
            </div>
        </div>

        {{-- MAHSULOTLAR --}}
        <div class="acc is-open" id="acc-mahsulot">
            <button type="button" class="acc__head" onclick="toggleAcc(this)">
                <span class="acc__head-left">
                    <span class="acc__icon">▤</span>
                    <span>Mahsulotlar</span>
                    <span class="acc__meta">{{ $products->count() }} ta</span>
                </span>
                <span class="acc__chevron">▾</span>
            </button>
            <div class="acc__body">
                <div style="padding:0 16px 12px;">
                    <input type="search" class="table-search" data-table="products-tbody" placeholder="Qidirish..."
                        style="max-width:280px;padding:8px 12px;border:1px solid #ddd;border-radius:8px;width:100%;">
                </div>
                <div class="acc__scroll" style="overflow-x:auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Mahsulot</th>
                                <th>Company</th>
                                <th>Ranglar / Soni</th>
                                <th>Retsept (1 dona)</th>
                                <th>Tan narx (1)</th>
                                <th>Jami summa</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="products-tbody">
                            @forelse ($products as $product)
                                @php
                                    $jamiSoni = (int) ($product->jami_soni ?? $product->ranglar->sum('soni'));
                                    $jamiSumma = round($product->tan_narxi * $jamiSoni, 2);
                                    $rangSearch = $product->ranglar->pluck('rangi')->implode(' ');
                                @endphp
                                <tr data-search="{{ strtolower($product->nomi . ' ' . $rangSearch) }}">
                                    <td>
                                        <p class="rank-item__name" style="margin:0;">{{ $product->nomi }}</p>
                                        <p class="rank-item__sub" style="margin:2px 0 0;">{{ $product->ranglar->count() }} rang</p>
                                    </td>
                                    <td>
                                        <p class="rank-item__name" style="margin:0;">{{ $product->company->nomi ?? '—' }}</p>
                                    </td>
                                    <td>
                                        @forelse ($product->ranglar as $rang)
                                            <span class="badge badge--new" style="margin:1px 4px 1px 0;">
                                                {{ $rang->rangi }} · <strong>{{ $rang->soni }}</strong>
                                            </span>
                                        @empty
                                            <span class="rank-item__sub">rang yo'q</span>
                                        @endforelse
                                        <div style="margin-top:4px;font-size:12px;opacity:.7;">Jami: {{ $jamiSoni }} dona</div>
                                    </td>
                                    <td>
                                        @forelse ($product->xomashyolar as $x)
                                            @php
                                                $b = rtrim(rtrim(number_format($x->pivot->sarf_miqdori, 3, '.', ''), '0'), '.');
                                            @endphp
                                            <span class="badge badge--new" style="margin:1px 4px 1px 0;">
                                                {{ $x->nomi }} · {{ $b }} {{ $x->birlik }}
                                            </span>
                                        @empty
                                            <span class="rank-item__sub">retsept yo'q</span>
                                        @endforelse
                                    </td>
                                    <td class="mono">{{ number_format($product->tan_narxi, 0, '.', ' ') }}</td>
                                    <td class="mono">{{ number_format($jamiSumma, 0, '.', ' ') }}</td>
                                    <td>
                                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                            <button type="button" class="card__link"
                                                style="background:none;border:none;cursor:pointer;font:inherit;"
                                                onclick="openProduceForProduct({{ $product->id }}, '{{ addslashes($product->nomi) }}')">Ishlab
                                                chiqarish</button>
                                            <button type="button" class="card__link"
                                                style="background:none;border:none;cursor:pointer;font:inherit;color:var(--sage);"
                                                onclick="openSellForProduct({{ $product->id }}, '{{ addslashes($product->nomi) }}')">Sotish</button>
                                            <a href="{{ route('admin.products.edit', $product) }}"
                                                class="card__link">Tahrirlash</a>
                                            <form action="{{ route('admin.products.destroy', $product) }}"
                                                method="POST"
                                                onsubmit="return confirm('«{{ $product->nomi }}» o\'chirilsinmi? Xomashyo qaytariladi.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="card__link"
                                                    style="background:none;border:none;cursor:pointer;color:var(--rust);font:inherit;padding:0;">O'chirish</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align:center;color:var(--ink-faint);padding:30px 0;">
                                        Mahsulot yo'q.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- XOMASHYOLAR --}}
        </div>

        <div class="acc is-open" id="acc-xomashyo">
            <button type="button" class="acc__head" onclick="toggleAcc(this)">
                <span class="acc__head-left">
                    <span class="acc__icon">▥</span>
                    <span>Xomashyolar</span>
                    <span class="acc__meta">{{ $xomashyolar->count() }} ta</span>
                </span>
                <span class="acc__chevron">▾</span>
            </button>
            <div class="acc__body">
                <div style="padding:0 16px 12px;">
                    <input type="search" class="table-search" data-table="xomashyo-tbody" placeholder="Qidirish..."
                        style="max-width:280px;padding:8px 12px;border:1px solid #ddd;border-radius:8px;width:100%;">
                </div>
                <div class="acc__scroll" style="overflow-x:auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nomi</th>
                                <th>Company</th>
                                <th>Rangi</th>
                                <th>Birlik</th>
                                <th>Narx</th>
                                <th>Qoldiq</th>
                                <th>Rulon</th>
                                <th>Qiymat</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="xomashyo-tbody">
                            @forelse ($xomashyolar as $x)
                                <tr
                                    data-search="{{ strtolower($x->nomi . ' ' . ($x->rangi ?? '') . ' ' . $x->birlik) }}">
                                    <td>
                                        <p class="rank-item__name" style="margin:0;">{{ $x->nomi }}</p>
                                    </td>
                                    <td>
                                        <p class="rank-item__name" style="margin:0;">{{ $x->company->nomi ?? '—' }}</p>
                                    </td>
                                    <td>{{ $x->rangi ?? '—' }}</td>
                                    <td>{{ $x->birlik }}</td>
                                    <td class="mono">{{ number_format($x->narxi_birlik_uchun, 0, '.', ' ') }}</td>
                                    <td class="mono">
                                        {{ rtrim(rtrim(number_format($x->ombordagi_qoldiq, 3, '.', ''), '0'), '.') }}
                                        {{ $x->birlik }}</td>
                                    <td class="mono">
                                        @if (in_array($x->birlik, ['kg', 'metr']) && $x->rulon_soni !== null)
                                            {{ $x->rulon_soni }} ta
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="mono">
                                        {{ number_format($x->narxi_birlik_uchun * $x->ombordagi_qoldiq, 0, '.', ' ') }}
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:10px;">
                                            <button type="button" class="card__link"
                                                style="background:none;border:none;cursor:pointer;font:inherit;"
                                                onclick="openXomashyoEdit({{ $x->id }}, '{{ addslashes($x->nomi) }}', '{{ addslashes($x->rangi ?? '') }}', '{{ $x->birlik }}', {{ $x->narxi_birlik_uchun }}, {{ $x->ombordagi_qoldiq }}, {{ $x->rulon_soni ?? 'null' }})">Tahrirlash</button>
                                            <form action="{{ route('admin.xomashyolar.destroy', $x) }}"
                                                method="POST"
                                                onsubmit="return confirm('«{{ $x->nomi }}» o\'chirilsinmi?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="card__link"
                                                    style="background:none;border:none;cursor:pointer;color:var(--rust);font:inherit;padding:0;">O'chirish</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8"
                                        style="text-align:center;color:var(--ink-faint);padding:30px 0;">Xomashyo yo'q.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- SOTUVLAR --}}
        </div>


        <div class="acc" id="acc-sotuv">
            <button type="button" class="acc__head" onclick="toggleAcc(this)">
                <span class="acc__head-left">
                    <span class="acc__icon">◎</span>
                    <span>Sotuvlar</span>
                    <span class="acc__meta">{{ $sotuvlar->count() }} ta</span>
                </span>
                <span class="acc__chevron">▾</span>
            </button>
            <div class="acc__body">
                <div style="padding:0 16px 12px;">
                    <input type="search" class="table-search" data-table="sotuv-tbody" placeholder="Qidirish..."
                        style="max-width:280px;padding:8px 12px;border:1px solid #ddd;border-radius:8px;width:100%;">
                </div>
                <div class="acc__scroll" style="overflow-x:auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sana</th>
                                <th>Mahsulot</th>
                                <th>Miqdor</th>
                                <th>Sotish narxi</th>
                                <th>Tan narx</th>
                                <th>Tushum</th>
                                <th>Foyda</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="sotuv-tbody">
                            @forelse ($sotuvlar as $s)
                                <tr data-search="{{ strtolower(($s->product->nomi ?? '') . ' ' . $s->sana) }}">
                                    <td class="mono">
                                        {{ \Illuminate\Support\Carbon::parse($s->sana)->format('d.m.Y') }}
                                    </td>
                                    <td>{{ $s->product->nomi ?? '—' }}@if($s->productRang) — {{ $s->productRang->rangi }}@endif</td>
                                    <td class="mono">{{ $s->miqdori }}</td>
                                    <td class="mono">{{ number_format($s->narxi_dona, 0, '.', ' ') }}</td>
                                    <td class="mono">{{ number_format($s->tan_narxi_dona, 0, '.', ' ') }}</td>
                                    <td class="mono">{{ number_format($s->jami_summa, 0, '.', ' ') }}</td>
                                    <td>
                                        <span class="badge {{ $s->foyda >= 0 ? 'badge--done' : 'badge--progress' }}">
                                            {{ $s->foyda >= 0 ? '+' : '' }}{{ number_format($s->foyda, 0, '.', ' ') }}
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="card__link"
                                            style="background:none;border:none;cursor:pointer;font:inherit;padding:0;margin-right:6px;"
                                            onclick='openEditSell(@json($s->id), @json($s->miqdori), @json((float) $s->narxi_dona), @json($s->sana))'>✎</button>
                                        <form action="{{ route('admin.sotuvlar.destroy', $s) }}" method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('Sotuv o\'chirilsinmi? Mahsulot omborga qaytadi.');">
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
                                        Sotuv yo'q.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ZARARLAR: yo'qotish + chiqim + brak --}}
        </div>


        <div class="acc" id="acc-zarar">
            <button type="button" class="acc__head" onclick="toggleAcc(this)">
                <span class="acc__head-left">
                    <span class="acc__icon">!</span>
                    <span>Zararlar tarixi</span>
                </span>
                <span class="acc__chevron">▾</span>
            </button>
            <div class="acc__body">
                <div class="acc__toolbar"><span class="acc__meta">
                        chiqim {{ number_format($stats['jami_chiqim'], 0, '.', ' ') }} +
                        yo'qotish {{ number_format($stats['jami_yoqotish'], 0, '.', ' ') }} +
                        brak {{ number_format($stats['jami_brak'], 0, '.', ' ') }} =
                        {{ number_format($stats['jami_zarar'], 0, '.', ' ') }} so'm
                    </span>
                </div>
                <div class="acc__scroll" style="overflow-x:auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sana</th>
                                <th>Turi</th>
                                <th>Nima</th>
                                <th>Sabab / izoh</th>
                                <th>Summa</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $zararlar = collect();
                                foreach ($chiqimlar as $c) {
                                    $zararlar->push([
                                        'sana' => $c->sana,
                                        'tur' => 'Chiqim',
                                        'nima' => $c->nomi . ($c->kategoriya ? " ({$c->kategoriya})" : ''),
                                        'sabab' => $c->izoh,
                                        'summa' => $c->summa,
                                        'delete' => route('admin.chiqimlar.destroy', $c),
                                    ]);
                                }
                                foreach ($yoqotishlar as $y) {
                                    $fmt = rtrim(rtrim(number_format($y->miqdori, 3, '.', ''), '0'), '.');
                                    $zararlar->push([
                                        'sana' => $y->sana,
                                        'tur' => "Yo'qotish",
                                        'nima' =>
                                            ($y->xomashyo->nomi ?? '—') . " · {$fmt} " . ($y->xomashyo->birlik ?? ''),
                                        'sabab' => $y->sabab . ($y->izoh ? ' / ' . $y->izoh : ''),
                                        'summa' => $y->summa,
                                        'delete' => route('admin.yoqotishlar.destroy', $y),
                                    ]);
                                }
                                foreach ($braklar as $b) {
                                    $zararlar->push([
                                        'sana' => $b->sana,
                                        'tur' => 'Brak',
                                        'nima' => ($b->product->nomi ?? '—') . ($b->productRang ? ' — ' . $b->productRang->rangi : '') . " · {$b->miqdori} dona",
                                        'sabab' => $b->sabab,
                                        'summa' => $b->summa,
                                        'delete' => route('admin.braklar.destroy', $b),
                                    ]);
                                }
                                $zararlar = $zararlar->sortByDesc('sana')->values();
                            @endphp
                            @forelse ($zararlar as $z)
                                <tr>
                                    <td class="mono">
                                        {{ \Illuminate\Support\Carbon::parse($z['sana'])->format('d.m.Y') }}</td>
                                    <td><span class="badge badge--progress">{{ $z['tur'] }}</span></td>
                                    <td>{{ $z['nima'] }}</td>
                                    <td>{{ $z['sabab'] ?? '—' }}</td>
                                    <td class="mono">{{ number_format($z['summa'], 0, '.', ' ') }} so'm</td>
                                    <td>
                                        <form action="{{ $z['delete'] }}" method="POST"
                                            onsubmit="return confirm('O\'chirilsinmi?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="card__link"
                                                style="background:none;border:none;cursor:pointer;color:var(--rust);font:inherit;padding:0;">O'chirish</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6"
                                        style="text-align:center;color:var(--ink-faint);padding:30px 0;">
                                        Zarar yozuvi yo'q.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- end-acc-zarar -->

    </div>

    {{-- ========== MODALS ========== --}}

    {{-- Xomashyo --}}
    <div class="modal-overlay" id="xomashyo-modal">
        <div class="modal" style="max-width:460px;">
            <div class="modal__head">
                <h2 id="xomashyo-modal-title">Yangi xomashyo</h2>
                <button type="button" class="modal__close" onclick="closeModal('xomashyo-modal')">&times;</button>
            </div>
            <form action="{{ route('admin.xomashyolar.store') }}" method="POST" id="xomashyo-form">
                @csrf
                <input type="hidden" name="_method" id="xomashyo-method" value="POST">
                <div class="modal__body">
                    <div class="form-grid" style="grid-template-columns:1fr 1fr;">
                        <div class="form-field">
                            <label>Nomi</label>
                            <input type="text" name="nomi" id="xomashyo-nomi" required>
                        </div>
                        <div class="form-field">
                            <label>Rangi</label>
                            <input type="text" name="rangi" id="xomashyo-rangi">
                        </div>
                    </div>
                    <div class="form-grid" style="grid-template-columns:1fr 1fr;margin-top:14px;">
                        <div class="form-field">
                            <label>Birlik</label>
                            <select name="birlik" id="xomashyo-birlik" required onchange="toggleRulonSoni()">
                                <option value="metr">metr</option>
                                <option value="kg">kg</option>
                                <option value="dona">dona</option>
                                <option value="litr">litr</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label>Narxi (1 birlik)</label>
                            <input type="number" step="0.01" min="0" name="narxi_birlik_uchun"
                                id="xomashyo-narxi" required>
                        </div>
                    </div>
                    <div class="form-grid" style="grid-template-columns:1fr 1fr;margin-top:14px;">
                        <div class="form-field">
                            <label>Qoldiq</label>
                            <input type="number" step="0.001" min="0" name="ombordagi_qoldiq"
                                id="xomashyo-qoldiq" value="0">
                        </div>
                        <div class="form-field" id="rulon-soni-field">
                            <label>Rulon soni</label>
                            <input type="number" step="1" min="0" name="rulon_soni"
                                id="xomashyo-rulon">
                        </div>
                    </div>
                </div>
                <div class="modal__foot">
                    <button type="button" class="btn btn--ghost"
                        onclick="closeModal('xomashyo-modal')">Bekor</button>
                    <button type="submit" class="btn btn--primary">Saqlash</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Produce --}}
    <div class="modal-overlay" id="produce-modal">
        <div class="modal" style="max-width:420px;">
            <div class="modal__head">
                <h2 id="produce-modal-title">Ishlab chiqarish</h2>
                <button type="button" class="modal__close" onclick="closeModal('produce-modal')">&times;</button>
            </div>
            <form id="produce-form" action="{{ route('admin.products.produce') }}" method="POST">
                @csrf
                <div class="modal__body">
                    <div class="form-field">
                        <label>Mahsulot + Rang</label>
                        <select name="product_rang_id" id="produce-rang" data-searchable required>
                            <option value="" disabled selected>Tanlang</option>
                            @foreach ($barchaRanglar ?? [] as $rg)
                                <option value="{{ $rg->id }}" data-product="{{ $rg->product_id }}">
                                    {{ $rg->product->nomi ?? '' }} — {{ $rg->rangi }} ({{ $rg->soni }} dona)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field" style="margin-top:12px;">
                        <label>Miqdori</label>
                        <input type="number" name="miqdori" min="1" required>
                    </div>
                </div>
                <div class="modal__foot">
                    <button type="button" class="btn btn--ghost"
                        onclick="closeModal('produce-modal')">Bekor</button>
                    <button type="submit" class="btn btn--primary">Ishlab chiqarish</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Sell --}}
    <div class="modal-overlay" id="sell-modal">
        <div class="modal" style="max-width:420px;">
            <div class="modal__head">
                <h2 id="sell-modal-title">Sotish</h2>
                <button type="button" class="modal__close" onclick="closeModal('sell-modal')">&times;</button>
            </div>
            <form id="sell-form" action="{{ route('admin.products.sell') }}" method="POST">
                @csrf
                <div class="modal__body">
                    <div class="form-field">
                        <label>Mahsulot + Rang</label>
                        <select name="product_rang_id" id="sell-rang" data-searchable required
                            onchange="updateSellStock()">
                            <option value="" disabled selected>Tanlang</option>
                            @foreach ($barchaRanglar ?? [] as $rg)
                                <option value="{{ $rg->id }}" data-product="{{ $rg->product_id }}" data-soni="{{ $rg->soni }}">
                                    {{ $rg->product->nomi ?? '' }} — {{ $rg->rangi }} ({{ $rg->soni }} dona)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="rank-item__sub" style="margin:8px 0 14px;" id="sell-modal-stock"></p>
                    <div class="form-grid" style="grid-template-columns:1fr 1fr;">
                        <div class="form-field">
                            <label>Miqdori</label>
                            <input type="number" name="miqdori" id="sell-miqdori" min="1" required>
                        </div>
                        <div class="form-field">
                            <label>1 dona narxi</label>
                            <input type="number" step="0.01" min="0" name="narxi_dona" id="sell-narxi" required>
                        </div>
                    </div>
                    <div class="form-field" style="margin-top:14px;">
                        <label>Sana</label>
                        <input type="date" name="sana" id="sell-sana" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal__foot">
                    <button type="button" class="btn btn--ghost" onclick="closeModal('sell-modal')">Bekor</button>
                    <button type="submit" class="btn btn--primary">Sotish</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="mahsulot-modal">
        <div class="modal" style="max-width:760px;">
            <div class="modal__head">
                <h2>Yangi mahsulot</h2>
                <button type="button" class="modal__close" onclick="closeModal('mahsulot-modal')">&times;</button>
            </div>
            <form action="{{ route('admin.products.store') }}" method="POST" id="mahsulot-form">
                @csrf
                <div class="modal__body">
                    <div class="form-grid" style="grid-template-columns:1fr 1fr;">
                        <div class="form-field">
                            <label>Nomi</label>
                            <input type="text" name="nomi" required value="{{ old('nomi') }}" placeholder="Sarafan">
                        </div>
                        <div class="form-field">
                            <label>Chevar puli (1 dona, so'm)</label>
                            <input type="number" name="chevar_puli" min="0" step="0.01"
                                value="{{ old('chevar_puli', 0) }}" oninput="recalcTotal()">
                        </div>
                    </div>
                    <div class="form-field" style="margin-top:14px;">
                        <label>Izoh</label>
                        <textarea name="izoh" rows="2">{{ old('izoh') }}</textarea>
                    </div>
                    <p class="rank-item__sub" style="margin-top:8px;">Tan narx = xomashyo + chevar puli. Ranglar bo‘yicha soni > 0 bo‘lsa xomashyo ayiriladi.</p>

                    <hr class="modal__divider">
                    <div class="card__head" style="margin-bottom:10px;">
                        <h2 style="font-size:14px;">Ranglar</h2>
                        <button type="button" class="card__link" onclick="addRangRow()">+ Rang</button>
                    </div>
                    <div id="rang-rows"></div>
                    <p class="rank-item__sub">Masalan: Qizil — 20, Ko‘k — 15. Retsept barcha ranglarga bir xil.</p>

                    <hr class="modal__divider">
                    <div class="card__head" style="margin-bottom:10px;">
                        <h2 style="font-size:14px;">Retsept — 1 dona uchun</h2>
                        <button type="button" class="card__link" onclick="addXomashyoRow()">+ Xomashyo</button>
                    </div>
                    <div id="xomashyo-rows"></div>
                    <div class="recipe-total">
                        <span>Taxminiy tan narxi:</span>
                        <strong class="mono" id="recipe-total-value">0 so'm</strong>
                    </div>

                    <hr class="modal__divider">
                    <div class="card__head" style="margin-bottom:10px;">
                        <h2 style="font-size:14px;">Chevarlar (kim tikdi — ixtiyoriy)</h2>
                        <button type="button" class="card__link" onclick="addChevarRow()">+ Chevar</button>
                    </div>
                    <div id="chevar-rows"></div>
                    <p class="rank-item__sub">Bir nechta chevar bo'lishi mumkin. Keyin Xodimlar da ham ko'rinadi /
                        to'ldiriladi.</p>
                </div>
                <div class="modal__foot">
                    <button type="button" class="btn btn--ghost"
                        onclick="closeModal('mahsulot-modal')">Bekor</button>
                    <button type="submit" class="btn btn--primary">Saqlash</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Yo'qotish --}}
    <div class="modal-overlay" id="yoqotish-modal">
        <div class="modal" style="max-width:420px;">
            <div class="modal__head">
                <h2>Yo'qotish (mato qoldig'i)</h2>
                <button type="button" class="modal__close" onclick="closeModal('yoqotish-modal')">&times;</button>
            </div>
            <form action="{{ route('admin.yoqotishlar.store') }}" method="POST">
                @csrf
                <div class="modal__body">
                    <p class="rank-item__sub" style="margin-bottom:12px;">Ishlatib bo'lmaydigan qoldiq ombordan
                        ayiriladi va zarar yoziladi.</p>
                    <div class="form-field">
                        <label>Xomashyo</label>
                        <select name="xomashyo_id" data-searchable required>
                            <option value="" disabled selected>Tanlang</option>
                            @foreach ($xomashyolar as $x)
                                <option value="{{ $x->id }}">{{ $x->nomi }}
                                    ({{ rtrim(rtrim(number_format($x->ombordagi_qoldiq, 3, '.', ''), '0'), '.') }}
                                    {{ $x->birlik }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field" style="margin-top:12px;">
                        <label>Miqdor</label>
                        <input type="number" name="miqdori" step="0.001" min="0.001" required>
                    </div>
                    <div class="form-field" style="margin-top:12px;">
                        <label>Sabab</label>
                        <input type="text" name="sabab" placeholder="Rulon qoldig'i">
                    </div>
                    <div class="form-field" style="margin-top:12px;">
                        <label>Sana</label>
                        <input type="date" name="sana" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal__foot">
                    <button type="button" class="btn btn--ghost"
                        onclick="closeModal('yoqotish-modal')">Bekor</button>
                    <button type="submit" class="btn btn--primary">Saqlash</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Chiqim --}}
    <div class="modal-overlay" id="chiqim-modal">
        <div class="modal" style="max-width:420px;">
            <div class="modal__head">
                <h2>Chiqim</h2>
                <button type="button" class="modal__close" onclick="closeModal('chiqim-modal')">&times;</button>
            </div>
            <form action="{{ route('admin.chiqimlar.store') }}" method="POST">
                @csrf
                <div class="modal__body">
                    <div class="form-field">
                        <label>Nomi</label>
                        <input type="text" name="nomi" required placeholder="Benzin / Obed">
                    </div>
                    <div class="form-field" style="margin-top:12px;">
                        <label>Kategoriya</label>
                        <select name="kategoriya">
                            <option value="obed">Obed</option>
                            <option value="benzin">Benzin</option>
                            <option value="ijara">Ijara</option>
                            <option value="boshqa" selected>Boshqa</option>
                        </select>
                    </div>
                    <div class="form-field" style="margin-top:12px;">
                        <label>Summa (so'm)</label>
                        <input type="number" name="summa" min="0.01" step="0.01" required>
                    </div>
                    <div class="form-field" style="margin-top:12px;">
                        <label>Sana</label>
                        <input type="date" name="sana" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-field" style="margin-top:12px;">
                        <label>Izoh</label>
                        <input type="text" name="izoh">
                    </div>
                </div>
                <div class="modal__foot">
                    <button type="button" class="btn btn--ghost" onclick="closeModal('chiqim-modal')">Bekor</button>
                    <button type="submit" class="btn btn--primary">Saqlash</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Brak --}}
    <div class="modal-overlay" id="brak-modal">
        <div class="modal" style="max-width:420px;">
            <div class="modal__head">
                <h2>Brak</h2>
                <button type="button" class="modal__close" onclick="closeModal('brak-modal')">&times;</button>
            </div>
            <form action="{{ route('admin.braklar.store') }}" method="POST">
                @csrf
                <div class="modal__body">
                    <p class="rank-item__sub" style="margin-bottom:12px;">Yaroqsiz mahsulot ombordan ayiriladi, zarar
                        yoziladi.</p>
                    <div class="form-field">
                        <label>Mahsulot + Rang</label>
                        <select name="product_rang_id" data-searchable required>
                            <option value="" disabled selected>Tanlang</option>
                            @foreach ($barchaRanglar ?? [] as $rg)
                                <option value="{{ $rg->id }}">
                                    {{ $rg->product->nomi ?? '' }} — {{ $rg->rangi }} ({{ $rg->soni }} dona)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field" style="margin-top:12px;">
                        <label>Miqdori</label>
                        <input type="number" name="miqdori" min="1" required>
                    </div>
                    <div class="form-field" style="margin-top:12px;">
                        <label>Sabab</label>
                        <input type="text" name="sabab" placeholder="Nuqson">
                    </div>
                    <div class="form-field" style="margin-top:12px;">
                        <label>Sana</label>
                        <input type="date" name="sana" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal__foot">
                    <button type="button" class="btn btn--ghost" onclick="closeModal('brak-modal')">Bekor</button>
                    <button type="submit" class="btn btn--primary">Saqlash</button>
                </div>
            </form>
        </div>
    </div>


    <div class="modal-overlay" id="edit-sell-modal">
        <div class="modal" style="max-width:420px;">
            <div class="modal__head">
                <h2>Sotuv tahrirlash</h2>
                <button type="button" class="modal__close" onclick="closeModal('edit-sell-modal')">&times;</button>
            </div>
            <form id="edit-sell-form" method="POST">
                @csrf
                @method('PUT')
                <div class="modal__body">
                    <div class="form-grid" style="grid-template-columns:1fr 1fr;">
                        <div class="form-field">
                            <label>Miqdori</label>
                            <input type="number" name="miqdori" id="es-miq" min="1" required>
                        </div>
                        <div class="form-field">
                            <label>1 dona narxi</label>
                            <input type="number" name="narxi_dona" id="es-narx" min="0" step="0.01"
                                required>
                        </div>
                    </div>
                    <div class="form-field" style="margin-top:14px;">
                        <label>Sana</label>
                        <input type="date" name="sana" id="es-sana">
                    </div>
                </div>
                <div class="modal__foot">
                    <button type="button" class="btn btn--ghost"
                        onclick="closeModal('edit-sell-modal')">Bekor</button>
                    <button type="submit" class="btn btn--primary">Saqlash</button>
                </div>
            </form>
        </div>
    </div>

    <template id="xomashyo-row-template">
        <div class="xomashyo-row">
            <select name="xomashyo_id[]" class="xomashyo-select" data-searchable required onchange="recalcTotal()">
                <option value="" disabled selected>Xomashyo tanlang</option>
                @foreach ($xomashyolar as $x)
                    <option value="{{ $x->id }}" data-narx="{{ $x->narxi_birlik_uchun }}">
                        {{ $x->nomi }} - {{$x->rangi}} ({{ number_format($x->narxi_birlik_uchun, 0, '.', ' ') }} /
                        {{ $x->birlik }})
                    </option>
                @endforeach
            </select>
            <input type="number" step="0.001" min="0.001" name="sarf_miqdori[]" placeholder="Miqdor"
                class="xomashyo-miqdor" required oninput="recalcTotal()">
            <button type="button" class="xomashyo-remove" onclick="removeXomashyoRow(this)">&times;</button>
        </div>
    </template>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.add('is-open');
        }

        function openEditSell(id, miq, narx, sana) {
            document.getElementById('edit-sell-form').action = '/admin/sotuvlar/' + id;
            document.getElementById('es-miq').value = miq;
            document.getElementById('es-narx').value = narx;
            document.getElementById('es-sana').value = (sana || '').toString().slice(0, 10);
            openModal('edit-sell-modal');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('is-open');
        }

        function toggleRulonSoni() {
            const b = document.getElementById('xomashyo-birlik').value;
            const show = (b === 'kg' || b === 'metr');
            document.getElementById('rulon-soni-field').style.display = show ? '' : 'none';
            if (!show) document.getElementById('xomashyo-rulon').value = '';
        }

        function openXomashyoCreate() {
            document.getElementById('xomashyo-modal-title').textContent = "Yangi xomashyo";
            document.getElementById('xomashyo-form').action = "{{ route('admin.xomashyolar.store') }}";
            document.getElementById('xomashyo-method').value = 'POST';
            document.getElementById('xomashyo-nomi').value = '';
            document.getElementById('xomashyo-rangi').value = '';
            document.getElementById('xomashyo-birlik').value = 'metr';
            document.getElementById('xomashyo-narxi').value = '';
            document.getElementById('xomashyo-qoldiq').value = 0;
            document.getElementById('xomashyo-rulon').value = '';
            toggleRulonSoni();
            openModal('xomashyo-modal');
        }

        function openXomashyoEdit(id, nomi, rangi, birlik, narxi, qoldiq, rulon) {
            document.getElementById('xomashyo-modal-title').textContent = "Xomashyo tahrirlash";
            document.getElementById('xomashyo-form').action = `/admin/xomashyolar/${id}`;
            document.getElementById('xomashyo-method').value = 'PUT';
            document.getElementById('xomashyo-nomi').value = nomi;
            document.getElementById('xomashyo-rangi').value = rangi;
            document.getElementById('xomashyo-birlik').value = birlik;
            document.getElementById('xomashyo-narxi').value = narxi;
            document.getElementById('xomashyo-qoldiq').value = qoldiq;
            document.getElementById('xomashyo-rulon').value = (rulon === null || rulon === undefined) ? '' : rulon;
            toggleRulonSoni();
            openModal('xomashyo-modal');
        }

        function openProduceForProduct(productId, nomi) {
            document.getElementById('produce-modal-title').textContent = `Ishlab chiqarish — ${nomi}`;
            const sel = document.getElementById('produce-rang');
            if (sel) {
                [...sel.options].forEach(o => {
                    if (!o.value) return;
                    o.hidden = (o.dataset.product != productId);
                });
                sel.value = '';
            }
            openModal('produce-modal');
        }

        function openSellForProduct(productId, nomi) {
            document.getElementById('sell-modal-title').textContent = `Sotish — ${nomi}`;
            const sel = document.getElementById('sell-rang');
            if (sel) {
                [...sel.options].forEach(o => {
                    if (!o.value) return;
                    o.hidden = (o.dataset.product != productId);
                });
                sel.value = '';
            }
            document.getElementById('sell-modal-stock').textContent = '';
            document.getElementById('sell-narxi').value = '';
            document.getElementById('sell-sana').value = new Date().toISOString().slice(0, 10);
            openModal('sell-modal');
        }

        function updateSellStock() {
            const sel = document.getElementById('sell-rang');
            const opt = sel?.selectedOptions?.[0];
            const soni = opt ? parseInt(opt.dataset.soni || 0, 10) : 0;
            document.getElementById('sell-modal-stock').textContent = opt && opt.value
                ? `Omborda: ${soni} dona.` : '';
            const miq = document.getElementById('sell-miqdori');
            if (miq) miq.max = soni || '';
        }

        function addRangRow() {
            const wrap = document.getElementById('rang-rows');
            if (!wrap) return;
            const idx = wrap.querySelectorAll('.xomashyo-row').length;
            const div = document.createElement('div');
            div.className = 'xomashyo-row';
            div.innerHTML = `
                <input type="text" name="ranglar[${idx}][rangi]" class="ss-input" placeholder="Rang nomi (masalan: Qizil)" required autocomplete="off">
                <input type="number" name="ranglar[${idx}][soni]" class="xomashyo-miqdor" min="0" value="0" placeholder="Soni" required>
                <button type="button" class="xomashyo-remove" onclick="this.parentElement.remove()" title="O'chirish">&times;</button>
            `;
            wrap.appendChild(div);
        }


        function addChevarRow() {
            const wrap = document.getElementById('chevar-rows');
            if (!wrap) return;
            const div = document.createElement('div');
            div.className = 'xomashyo-row';
            div.innerHTML = `
                <select name="chevar_id[]">
                    <option value="">Chevar</option>
                    @foreach ($chevarlar ?? collect() as $ch)
                        <option value="{{ $ch->id }}">{{ $ch->toliq_ism }}</option>
                    @endforeach
                </select>
                <input type="number" name="chevar_miqdori[]" min="1" placeholder="dona">
                <input type="number" name="chevar_pul[]" min="0" step="0.01" placeholder="1 dona so'm">
                <input type="text" name="chevar_izoh[]" placeholder="yeng/asosi">
                <button type="button" class="xomashyo-remove" onclick="this.parentElement.remove()">&times;</button>
            `;
            wrap.appendChild(div);
        }

        function addXomashyoRow() {
            const t = document.getElementById('xomashyo-row-template');
            document.getElementById('xomashyo-rows').appendChild(t.content.cloneNode(true));
        }

        function removeXomashyoRow(btn) {
            btn.closest('.xomashyo-row').remove();
            recalcTotal();
        }

        function recalcTotal() {
            let total = 0;
            document.querySelectorAll('#xomashyo-rows .xomashyo-row').forEach(row => {
                const sel = row.querySelector('.xomashyo-select');
                const mq = row.querySelector('.xomashyo-miqdor');
                if (!sel || !mq) return;
                const narx = sel.selectedOptions[0] ? parseFloat(sel.selectedOptions[0].dataset.narx || 0) : 0;
                total += narx * parseFloat(mq.value || 0);
            });
            const chevar = parseFloat(document.querySelector('[name=chevar_puli]')?.value || 0);
            total += chevar;
            document.getElementById('recipe-total-value').textContent =
                new Intl.NumberFormat('uz-UZ').format(Math.round(total)) + " so'm";
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('xomashyo-rows') && document.getElementById('xomashyo-rows').children.length === 0) addXomashyoRow();
            if (document.getElementById('rang-rows') && document.getElementById('rang-rows').children.length === 0) addRangRow();
            toggleRulonSoni();
            document.querySelectorAll('.table-search').forEach(input => {
                input.addEventListener('input', () => {
                    const q = input.value.toLowerCase().trim();
                    const tbody = document.getElementById(input.dataset.table);
                    if (!tbody) return;
                    tbody.querySelectorAll('tr[data-search]').forEach(tr => {
                        tr.style.display = (!q || tr.dataset.search.includes(q)) ? '' :
                            'none';
                    });
                });
            });
        });

        document.getElementById('mahsulot-form')?.addEventListener('submit', function(e) {
            if (document.querySelectorAll('#xomashyo-rows .xomashyo-row').length === 0) {
                e.preventDefault();
                alert("Kamida bitta xomashyo qo'shing.");
                return;
            }
            if (document.querySelectorAll('#rang-rows .xomashyo-row').length === 0) {
                e.preventDefault();
                alert("Kamida bitta rang qo'shing.");
            }
        });
    </script>


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
