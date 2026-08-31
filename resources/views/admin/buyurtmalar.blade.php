<x-layouts.sidebar title="Buyurtmalar">

    <div class="content">

        @if (session('success'))
            <div class="alert alert--success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert--error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert--error">
                @foreach ($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        @endif

        {{-- Filter --}}
        <form method="GET" action="{{ route('admin.buyurtmalar.index') }}"
              style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;align-items:end;">
            <div class="form-field" style="margin:0;min-width:140px;">
                <label style="font-size:12px;">Holat</label>
                <select name="holat">
                    <option value="">Barchasi</option>
                    @foreach (\App\Models\Buyurtma::HOLATLAR as $k => $label)
                        <option value="{{ $k }}" @selected(($holat ?? '') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-field" style="margin:0;min-width:180px;">
                <label style="font-size:12px;">Qidiruv</label>
                <input type="search" name="q" value="{{ $q ?? '' }}" placeholder="mijoz, mahsulot, tel...">
            </div>
            <button type="submit" class="btn btn--primary">Filter</button>
            <a href="{{ route('admin.buyurtmalar.index') }}" class="btn btn--ghost">Tozalash</a>
            <button type="button" class="btn btn--primary" style="margin-left:auto;"
                    onclick="openModal('buyurtma-modal')">+ Buyurtma</button>
        </form>

        {{-- Stats --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-card__top"><span class="stat-card__label">Yangi</span></div>
                <div class="stat-card__value mono">{{ $stats['yangi'] }}</div>
            </div>
            <div class="stat-card stat-card--gold">
                <div class="stat-card__top"><span class="stat-card__label">Jarayonda</span></div>
                <div class="stat-card__value mono">{{ $stats['jarayonda'] }}</div>
            </div>
            <div class="stat-card stat-card--sage">
                <div class="stat-card__top"><span class="stat-card__label">Bajarildi</span></div>
                <div class="stat-card__value mono">{{ $stats['bajarildi'] }}</div>
            </div>
            <div class="stat-card stat-card--rust">
                <div class="stat-card__top"><span class="stat-card__label">Jami foyda (bajarilgan)</span></div>
                <div class="stat-card__value mono">{{ number_format($stats['jami_foyda'], 0, '.', ' ') }}</div>
            </div>
        </div>

        {{-- Jadval --}}
        <div class="card table-card">
            <div class="card__head">
                <h2>Buyurtmalar</h2>
                <span class="card__meta">{{ $buyurtmalar->count() }} ta</span>
            </div>
            <div style="overflow-x:auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sana</th>
                            <th>Mijoz</th>
                            <th>Mahsulot / buyurtma</th>
                            <th>Miqdor</th>
                            <th>Narx</th>
                            <th>Jami</th>
                            <th>Foyda</th>
                            <th>Muddat</th>
                            <th>Holat</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($buyurtmalar as $b)
                            <tr>
                                <td class="mono" data-label="Sana">
                                    {{ $b->sana ? $b->sana->format('d.m.Y') : '—' }}
                                </td>
                                <td data-label="Mijoz">
                                    <p class="rank-item__name" style="margin:0;">{{ $b->mijoz_nomi }}</p>
                                    @if ($b->mijoz_tel)
                                        <p class="rank-item__sub" style="margin:2px 0 0;">{{ $b->mijoz_tel }}</p>
                                    @endif
                                </td>
                                <td data-label="Mahsulot">
                                    <p class="rank-item__name" style="margin:0;">{{ $b->nomi }}</p>
                                    @if (! $b->product_id)
                                        <p class="rank-item__sub" style="margin:2px 0 0;">Mahsulot bog'lanmagan</p>
                                    @endif
                                </td>
                                <td class="mono" data-label="Miqdor">{{ $b->miqdori }}</td>
                                <td class="mono" data-label="Narx">{{ number_format($b->narxi_dona, 0, '.', ' ') }}</td>
                                <td class="mono" data-label="Jami">{{ number_format($b->jami_summa, 0, '.', ' ') }}</td>
                                <td data-label="Foyda">
                                    <span class="badge {{ $b->foyda >= 0 ? 'badge--done' : 'badge--progress' }}">
                                        {{ $b->foyda >= 0 ? '+' : '' }}{{ number_format($b->foyda, 0, '.', ' ') }}
                                    </span>
                                </td>
                                <td class="mono" data-label="Muddat">
                                    {{ $b->muddat ? $b->muddat->format('d.m.Y') : '—' }}
                                </td>
                                <td data-label="Holat">
                                    @if ($b->holat === 'yangi')
                                        <span class="badge badge--new">Yangi</span>
                                    @elseif ($b->holat === 'jarayonda')
                                        <span class="badge badge--progress">Jarayonda</span>
                                    @elseif ($b->holat === 'bajarildi')
                                        <span class="badge badge--done">Bajarildi</span>
                                    @else
                                        <span class="badge" style="background:rgba(239,68,68,.12);color:var(--rust);">Bekor</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                                        @if ($b->holat !== 'bajarildi' && $b->holat !== 'bekor')
                                            <form method="POST" action="{{ route('admin.buyurtmalar.holat', $b) }}" style="display:inline;">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="holat" value="bajarildi">
                                                <button type="submit" class="card__link"
                                                    style="background:none;border:none;cursor:pointer;color:var(--sage);font:inherit;padding:0;">
                                                    ✓ Bajarildi
                                                </button>
                                            </form>
                                            @if ($b->holat === 'yangi')
                                                <form method="POST" action="{{ route('admin.buyurtmalar.holat', $b) }}" style="display:inline;">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="holat" value="jarayonda">
                                                    <button type="submit" class="card__link"
                                                        style="background:none;border:none;cursor:pointer;font:inherit;padding:0;">
                                                        Jarayonda
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                        <button type="button" class="card__link"
                                            style="background:none;border:none;cursor:pointer;font:inherit;padding:0;"
                                            onclick='openEditBuyurtma(@json($b))'>✎</button>
                                        <form method="POST" action="{{ route('admin.buyurtmalar.destroy', $b) }}"
                                              style="display:inline;"
                                              onsubmit="return confirm('O\'chirilsinmi?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="card__link"
                                                style="background:none;border:none;cursor:pointer;color:var(--rust);font:inherit;padding:0;">×</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" style="text-align:center;color:var(--ink-faint);padding:30px 0;">
                                    Buyurtma yo'q. «+ Buyurtma» bosing.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Yangi / tahrirlash modal --}}
    <div class="modal-overlay" id="buyurtma-modal">
        <div class="modal" style="max-width:560px;">
            <div class="modal__head">
                <h2 id="buyurtma-modal-title">Yangi buyurtma</h2>
                <button type="button" class="modal__close" onclick="closeModal('buyurtma-modal')">&times;</button>
            </div>
            <form id="buyurtma-form" method="POST" action="{{ route('admin.buyurtmalar.store') }}">
                @csrf
                <input type="hidden" name="_method" id="buyurtma-method" value="POST">
                <div class="modal__body">
                    <div class="form-grid" style="grid-template-columns:1fr 1fr;">
                        <div class="form-field">
                            <label>Mijoz ismi</label>
                            <input type="text" name="mijoz_ism" id="b-mijoz-ism" placeholder="Dilnoza Karimova">
                        </div>
                        <div class="form-field">
                            <label>Telefon</label>
                            <input type="text" name="mijoz_tel" id="b-mijoz-tel" placeholder="901234567">
                        </div>
                    </div>

                    @if ($mijozlar->isNotEmpty())
                        <div class="form-field" style="margin-top:12px;">
                            <label>Yoki tizimdagi mijoz</label>
                            <select name="mijoz_id" id="b-mijoz-id" data-searchable>
                                <option value="">—</option>
                                @foreach ($mijozlar as $m)
                                    <option value="{{ $m->id }}">{{ $m->toliq_ism }} ({{ $m->tel_nomer }})</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <hr class="modal__divider">

                    <div class="form-field">
                        <label>Bizdagi mahsulot (ixtiyoriy — yo'q bo'lsa bo'sh qoldiring)</label>
                        <select name="product_id" id="b-product" data-searchable onchange="onBuyurtmaProductChange()">
                            <option value="">— Hali bog'lanmagan —</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}" data-tan="{{ $p->hisoblaTanNarxi() }}">{{ $p->nomi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field" style="margin-top:12px;">
                        <label>Rang (bizdagi)</label>
                        <select name="product_rang_id" id="b-rang" data-searchable>
                            <option value="">—</option>
                            @foreach ($barchaRanglar as $rg)
                                <option value="{{ $rg->id }}" data-product="{{ $rg->product_id }}">
                                    {{ $rg->product->nomi ?? '' }} — {{ $rg->rangi }} ({{ $rg->soni }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-grid" style="grid-template-columns:1fr 1fr;margin-top:12px;">
                        <div class="form-field">
                            <label>Buyurtma nomi (matn)</label>
                            <input type="text" name="buyurtma_nomi" id="b-nomi" placeholder="Agar mahsulot yo'q">
                        </div>
                        <div class="form-field">
                            <label>Rang (matn)</label>
                            <input type="text" name="rangi" id="b-rangi" placeholder="Qizil / Ko'k">
                        </div>
                    </div>

                    <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr;margin-top:12px;">
                        <div class="form-field">
                            <label>Miqdor</label>
                            <input type="number" name="miqdori" id="b-miq" min="1" value="1" required>
                        </div>
                        <div class="form-field">
                            <label>1 dona narxi</label>
                            <input type="number" name="narxi_dona" id="b-narx" min="0" step="0.01" required>
                        </div>
                        <div class="form-field">
                            <label>Tan narx (1)</label>
                            <input type="number" name="tan_narxi_dona" id="b-tan" min="0" step="0.01"
                                   placeholder="avto">
                        </div>
                    </div>

                    <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr;margin-top:12px;">
                        <div class="form-field">
                            <label>Muddat</label>
                            <input type="date" name="muddat" id="b-muddat">
                        </div>
                        <div class="form-field">
                            <label>Sana</label>
                            <input type="date" name="sana" id="b-sana" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-field">
                            <label>Holat</label>
                            <select name="holat" id="b-holat">
                                @foreach (\App\Models\Buyurtma::HOLATLAR as $k => $label)
                                    <option value="{{ $k }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-field" style="margin-top:12px;">
                        <label>Izoh</label>
                        <input type="text" name="izoh" id="b-izoh">
                    </div>
                </div>
                <div class="modal__foot">
                    <button type="button" class="btn btn--ghost" onclick="closeModal('buyurtma-modal')">Bekor</button>
                    <button type="submit" class="btn btn--primary">Saqlash</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            var el = document.getElementById(id);
            if (el) el.classList.add('is-open');
        }
        function closeModal(id) {
            document.getElementById(id)?.classList.remove('is-open');
        }

        function onBuyurtmaProductChange() {
            var sel = document.getElementById('b-product');
            var opt = sel?.selectedOptions?.[0];
            var tan = opt ? opt.getAttribute('data-tan') : '';
            var tanInput = document.getElementById('b-tan');
            if (tanInput && tan && !tanInput.value) {
                tanInput.value = tan;
            }
            // Rang filter
            var pid = sel?.value || '';
            document.querySelectorAll('#b-rang option').forEach(function (o) {
                if (!o.value) return;
                o.hidden = pid && o.getAttribute('data-product') !== pid;
            });
        }

        function openEditBuyurtma(b) {
            document.getElementById('buyurtma-modal-title').textContent = 'Buyurtma tahrirlash';
            document.getElementById('buyurtma-form').action = '/admin/buyurtmalar/' + b.id;
            document.getElementById('buyurtma-method').value = 'PUT';

            document.getElementById('b-mijoz-ism').value = b.mijoz_ism || '';
            document.getElementById('b-mijoz-tel').value = b.mijoz_tel || '';
            var mijozSel = document.getElementById('b-mijoz-id');
            if (mijozSel) mijozSel.value = b.mijoz_id || '';

            document.getElementById('b-product').value = b.product_id || '';
            document.getElementById('b-rang').value = b.product_rang_id || '';
            document.getElementById('b-nomi').value = b.buyurtma_nomi || '';
            document.getElementById('b-rangi').value = b.rangi || '';
            document.getElementById('b-miq').value = b.miqdori || 1;
            document.getElementById('b-narx').value = b.narxi_dona || 0;
            document.getElementById('b-tan').value = b.tan_narxi_dona != null ? b.tan_narxi_dona : '';
            document.getElementById('b-muddat').value = (b.muddat || '').toString().slice(0, 10);
            document.getElementById('b-sana').value = (b.sana || '').toString().slice(0, 10);
            document.getElementById('b-holat').value = b.holat || 'yangi';
            document.getElementById('b-izoh').value = b.izoh || '';

            onBuyurtmaProductChange();
            openModal('buyurtma-modal');
        }

        // Yangi modal ochishda tozalash
        document.querySelector('[onclick*="buyurtma-modal"]')?.addEventListener('click', function () {
            document.getElementById('buyurtma-modal-title').textContent = 'Yangi buyurtma';
            document.getElementById('buyurtma-form').action = "{{ route('admin.buyurtmalar.store') }}";
            document.getElementById('buyurtma-method').value = 'POST';
            document.getElementById('buyurtma-form').reset();
            document.getElementById('b-sana').value = new Date().toISOString().slice(0, 10);
            document.getElementById('b-holat').value = 'yangi';
        });
    </script>
</x-layouts.sidebar>
