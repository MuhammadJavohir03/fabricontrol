<x-layouts.sidebar title="Mahsulot tahrirlash">

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
                <h2>«{{ $product->nomi }}» tahrirlash</h2>
                <a href="{{ route('admin.products.index') }}" class="card__link">← Orqaga</a>
            </div>

            <form action="{{ route('admin.products.update', $product) }}" method="POST" style="padding:18px 22px 24px;">
                @csrf
                @method('PUT')

                <div class="form-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="form-field">
                        <label>Nomi</label>
                        <input type="text" name="nomi" required value="{{ old('nomi', $product->nomi) }}">
                    </div>
                    <div class="form-field">
                        <label>Chevar puli (1 dona) — tan narxga qo'shiladi</label>
                        <input type="number" name="chevar_puli" min="0" step="0.01"
                               value="{{ old('chevar_puli', $product->chevar_puli) }}"
                               oninput="recalcTotal()">
                    </div>
                </div>

                <div class="form-field" style="margin-top:14px;">
                    <label>Izoh</label>
                    <textarea name="izoh" rows="2">{{ old('izoh', $product->izoh) }}</textarea>
                </div>

                <p class="rank-item__sub" style="margin-top:8px;">
                    Tan narx = xomashyo + chevar puli. Ranglar soni ishlab chiqarish / sotish / brak orqali o'zgaradi.
                </p>

                <hr class="modal__divider">

                {{-- RANGLAR --}}
                <div class="card__head" style="margin-bottom:10px;">
                    <h2 style="font-size:14px;">Ranglar</h2>
                    <button type="button" class="card__link" onclick="addEditRangRow()">+ Rang</button>
                </div>
                <div id="edit-rang-rows">
                    @foreach ($product->ranglar as $i => $rang)
                        <div class="xomashyo-row">
                            <input type="hidden" name="ranglar[{{ $i }}][id]" value="{{ $rang->id }}">
                            <input type="text"
                                   name="ranglar[{{ $i }}][rangi]"
                                   class="ss-input"
                                   value="{{ $rang->rangi }}"
                                   required
                                   autocomplete="off"
                                   placeholder="Rang nomi">
                            <input type="number"
                                   name="ranglar[{{ $i }}][soni]"
                                   class="xomashyo-miqdor"
                                   min="0"
                                   step="1"
                                   value="{{ old('ranglar.'.$i.'.soni', $rang->soni) }}"
                                   required
                                   title="Sonini o'zgartirsangiz, xomashyo avtomatik hisoblanadi (ayiriladi/qaytariladi)">
                            @if ((int) $rang->soni === 0)
                                <button type="button" class="xomashyo-remove"
                                        onclick="this.closest('.xomashyo-row').remove()"
                                        title="O'chirish">&times;</button>
                            @else
                                <span class="xomashyo-remove"
                                      style="opacity:.35;cursor:not-allowed;display:grid;place-items:center;"
                                      title="O'chirish uchun avval sonini 0 ga tushiring va saqlang">·</span>
                            @endif
                        </div>
                    @endforeach
                </div>
                <p class="rank-item__sub">
                    Rang nomini va sonini o'zgartirish mumkin — soni o'zgarganda tegishli xomashyo
                    avtomatik ayiriladi (soni oshsa) yoki omborga qaytariladi (soni kamaysa),
                    va bu ishlab chiqarish tarixiga yoziladi.
                    Yangi rang qo'shilsa, kiritilgan soni bo'yicha xomashyo darhol hisoblanadi.
                    O'chirish uchun avval sonini 0 ga tushirib saqlang.
                </p>

                <hr class="modal__divider">

                {{-- RETSEPT --}}
                <div class="card__head" style="margin-bottom:10px;">
                    <h2 style="font-size:14px;">Retsept — 1 dona uchun</h2>
                    <button type="button" class="card__link" onclick="addXomashyoRow()">+ Xomashyo</button>
                </div>

                <div id="xomashyo-rows">
                    @foreach ($product->xomashyolar as $x)
                        <div class="xomashyo-row">
                            <div class="search-select" style="flex:1;position:relative;">
                                <input type="text" class="ss-input" placeholder="Qidirish..." autocomplete="off"
                                       value="{{ $x->nomi }}" onfocus="ssOpen(this)" oninput="ssFilter(this)">
                                <input type="hidden" name="xomashyo_id[]" class="ss-value" value="{{ $x->id }}"
                                       data-narx="{{ $x->narxi_birlik_uchun }}">
                                <div class="ss-list"
                                     style="display:none;position:absolute;z-index:20;left:0;right:0;max-height:180px;overflow:auto;background:#fff;border:1px solid #ddd;border-radius:8px;">
                                    @foreach ($xomashyolar as $opt)
                                        <div class="ss-item" data-id="{{ $opt->id }}"
                                             data-narx="{{ $opt->narxi_birlik_uchun }}"
                                             data-label="{{ $opt->nomi }}"
                                             onclick="ssPick(this)">{{ $opt->nomi }}
                                            ({{ number_format($opt->narxi_birlik_uchun, 0, '.', ' ') }}/{{ $opt->birlik }})
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <input type="number" step="0.001" min="0.001" name="sarf_miqdori[]"
                                   class="xomashyo-miqdor"
                                   value="{{ $x->pivot->sarf_miqdori }}" required oninput="recalcTotal()">
                            <button type="button" class="xomashyo-remove"
                                    onclick="removeXomashyoRow(this)">&times;</button>
                        </div>
                    @endforeach
                </div>

                <div class="recipe-total" style="margin-top:12px;">
                    <span>Taxminiy tan narxi:</span>
                    <strong class="mono" id="recipe-total-value">0 so'm</strong>
                </div>

                <div style="margin-top:24px;display:flex;gap:10px;justify-content:flex-end;">
                    <a href="{{ route('admin.products.index') }}" class="btn btn--ghost">Bekor</a>
                    <button type="submit" class="btn btn--primary">Saqlash</button>
                </div>
            </form>
        </div>
    </div>

    <template id="xomashyo-row-template">
        <div class="xomashyo-row">
            <div class="search-select" style="flex:1;position:relative;">
                <input type="text" class="ss-input" placeholder="Xomashyo qidirish..." autocomplete="off"
                       onfocus="ssOpen(this)" oninput="ssFilter(this)">
                <input type="hidden" name="xomashyo_id[]" class="ss-value" value="" data-narx="0">
                <div class="ss-list"
                     style="display:none;position:absolute;z-index:20;left:0;right:0;max-height:180px;overflow:auto;background:#fff;border:1px solid #ddd;border-radius:8px;">
                    @foreach ($xomashyolar as $opt)
                        <div class="ss-item" data-id="{{ $opt->id }}" data-narx="{{ $opt->narxi_birlik_uchun }}"
                             data-label="{{ $opt->nomi }}"
                             onclick="ssPick(this)">{{ $opt->nomi }}
                            ({{ number_format($opt->narxi_birlik_uchun, 0, '.', ' ') }}/{{ $opt->birlik }})
                        </div>
                    @endforeach
                </div>
            </div>
            <input type="number" step="0.001" min="0.001" name="sarf_miqdori[]" placeholder="Miqdor"
                   class="xomashyo-miqdor" required oninput="recalcTotal()">
            <button type="button" class="xomashyo-remove" onclick="removeXomashyoRow(this)">&times;</button>
        </div>
    </template>

    <script>
        function addXomashyoRow() {
            const t = document.getElementById('xomashyo-row-template');
            document.getElementById('xomashyo-rows').appendChild(t.content.cloneNode(true));
        }

        function removeXomashyoRow(btn) {
            btn.closest('.xomashyo-row').remove();
            recalcTotal();
        }

        function addEditRangRow() {
            const wrap = document.getElementById('edit-rang-rows');
            if (!wrap) return;
            const idx = wrap.querySelectorAll('.xomashyo-row').length;
            const div = document.createElement('div');
            div.className = 'xomashyo-row';
            div.innerHTML = `
                <input type="text" name="ranglar[${idx}][rangi]" class="ss-input"
                       placeholder="Rang nomi (masalan: Qizil)" required autocomplete="off">
                <input type="number" name="ranglar[${idx}][soni]" class="xomashyo-miqdor"
                       min="0" step="1" value="0"
                       title="Boshlang'ich soni — kiritilsa xomashyo shu zahoti ayiriladi">
                <button type="button" class="xomashyo-remove"
                        onclick="this.closest('.xomashyo-row').remove()" title="O'chirish">&times;</button>
            `;
            wrap.appendChild(div);
        }

        function recalcTotal() {
            let total = 0;
            document.querySelectorAll('#xomashyo-rows .xomashyo-row').forEach(row => {
                const hid = row.querySelector('.ss-value');
                const mq = row.querySelector('.xomashyo-miqdor');
                total += parseFloat(hid?.dataset.narx || 0) * parseFloat(mq?.value || 0);
            });
            total += parseFloat(document.querySelector('[name=chevar_puli]')?.value || 0);
            document.getElementById('recipe-total-value').textContent =
                new Intl.NumberFormat('uz-UZ').format(Math.round(total)) + " so'm";
        }

        function ssOpen(input) {
            const list = input.parentElement.querySelector('.ss-list');
            document.querySelectorAll('.ss-list').forEach(l => {
                if (l !== list) l.style.display = 'none';
            });
            list.style.display = 'block';
            ssFilter(input);
        }

        function ssFilter(input) {
            const q = input.value.toLowerCase();
            input.parentElement.querySelectorAll('.ss-item').forEach(item => {
                item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        }

        function ssPick(item) {
            const box = item.closest('.search-select');
            box.querySelector('.ss-input').value = item.dataset.label;
            const hid = box.querySelector('.ss-value');
            hid.value = item.dataset.id;
            hid.dataset.narx = item.dataset.narx;
            box.querySelector('.ss-list').style.display = 'none';
            recalcTotal();
        }

        document.addEventListener('click', e => {
            if (!e.target.closest('.search-select')) {
                document.querySelectorAll('.ss-list').forEach(l => l.style.display = 'none');
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('xomashyo-rows').children.length === 0) addXomashyoRow();
            if (document.getElementById('edit-rang-rows').children.length === 0) addEditRangRow();
            recalcTotal();
        });
    </script>

</x-layouts.sidebar>