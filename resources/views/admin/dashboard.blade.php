<x-layouts.sidebar title="Statistika">

    <div class="content">

        {{-- SALOMLASHUV --}}
        <div class="card hero">
            <div class="hero__body">
                <p class="hero__eyebrow">{{ now()->translatedFormat('d-F, l') }}</p>
                <h2>Salom, {{ auth()->user()->toliq_ism ?? 'Admin' }}</h2>
                <p>Ishlab chiqarish, sotuv va buyurtmalar bo'yicha jonli hisobot.</p>
            </div>
            <div class="hero__figure">Hisobot<br>paneli</div>
        </div>

        {{-- ASOSIY KO'RSATKICHLAR --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-card__top">
                    <span class="stat-card__label">Mahsulot turlari</span>
                </div>
                <div class="stat-card__value mono">{{ $stats['jami_mahsulotlar'] }}</div>
            </div>
            <div class="stat-card stat-card--rust">
                <div class="stat-card__top">
                    <span class="stat-card__label">Jami buyurtmalar</span>
                </div>
                <div class="stat-card__value mono">{{ $stats['jami_buyurtmalar'] }}</div>
            </div>
            <div class="stat-card stat-card--gold">
                <div class="stat-card__top">
                    <span class="stat-card__label">Sof foyda</span>
                </div>
                <div class="stat-card__value mono">{{ number_format($stats['jami_foyda'], 0, '.', ' ') }}</div>
            </div>
            <div class="stat-card stat-card--sage">
                <div class="stat-card__top">
                    <span class="stat-card__label">Yangi buyurtmalar</span>
                    @if ($stats['yangi_buyurtmalar'] > 0)
                        <span class="stat-card__trend stat-card__trend--warn">Diqqat</span>
                    @endif
                </div>
                <div class="stat-card__value mono">{{ $stats['yangi_buyurtmalar'] }}</div>
            </div>
        </div>

        {{-- QO'SHIMCHA QISQA STAT --}}
        <div class="stat-grid" style="margin-top:0;">
            <div class="stat-card">
                <div class="stat-card__top"><span class="stat-card__label">Jami tushum</span></div>
                <div class="stat-card__value mono" style="font-size:22px;">{{ number_format($stats['jami_tushum'], 0, '.', ' ') }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__top"><span class="stat-card__label">Jami zarar</span></div>
                <div class="stat-card__value mono" style="font-size:22px;">{{ number_format($stats['jami_zarar'], 0, '.', ' ') }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__top"><span class="stat-card__label">Sotuvlar soni</span></div>
                <div class="stat-card__value mono" style="font-size:22px;">{{ $stats['jami_sotuvlar'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__top"><span class="stat-card__label">Xomashyo qiymati</span></div>
                <div class="stat-card__value mono" style="font-size:22px;">{{ number_format($stats['xomashyo_qiymati'], 0, '.', ' ') }}</div>
            </div>
        </div>

        {{-- HAFTA / OY / YIL --}}
        <div class="card table-card" style="margin-bottom:20px;">
            <div class="card__head">
                <h2>Davriy hisobot</h2>
                <span class="card__meta">hafta · oy · yil</span>
            </div>
            <div style="overflow-x:auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ko'rsatkich</th>
                            <th>Shu hafta</th>
                            <th>Shu oy</th>
                            <th>Shu yil</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td data-label="Ko'rsatkich"><strong>Tushum</strong></td>
                            <td class="mono" data-label="Hafta">{{ number_format($periods['hafta']['tushum'], 0, '.', ' ') }}</td>
                            <td class="mono" data-label="Oy">{{ number_format($periods['oy']['tushum'], 0, '.', ' ') }}</td>
                            <td class="mono" data-label="Yil">{{ number_format($periods['yil']['tushum'], 0, '.', ' ') }}</td>
                        </tr>
                        <tr>
                            <td data-label="Ko'rsatkich"><strong>Sof foyda</strong></td>
                            <td class="mono" data-label="Hafta">
                                <span class="badge {{ $periods['hafta']['sof_foyda'] >= 0 ? 'badge--done' : 'badge--progress' }}">
                                    {{ number_format($periods['hafta']['sof_foyda'], 0, '.', ' ') }}
                                </span>
                            </td>
                            <td class="mono" data-label="Oy">
                                <span class="badge {{ $periods['oy']['sof_foyda'] >= 0 ? 'badge--done' : 'badge--progress' }}">
                                    {{ number_format($periods['oy']['sof_foyda'], 0, '.', ' ') }}
                                </span>
                            </td>
                            <td class="mono" data-label="Yil">
                                <span class="badge {{ $periods['yil']['sof_foyda'] >= 0 ? 'badge--done' : 'badge--progress' }}">
                                    {{ number_format($periods['yil']['sof_foyda'], 0, '.', ' ') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td data-label="Ko'rsatkich">Sotuv (dona / yozuv)</td>
                            <td class="mono" data-label="Hafta">{{ $periods['hafta']['sotuv_dona'] }} / {{ $periods['hafta']['sotuv_soni'] }}</td>
                            <td class="mono" data-label="Oy">{{ $periods['oy']['sotuv_dona'] }} / {{ $periods['oy']['sotuv_soni'] }}</td>
                            <td class="mono" data-label="Yil">{{ $periods['yil']['sotuv_dona'] }} / {{ $periods['yil']['sotuv_soni'] }}</td>
                        </tr>
                        <tr>
                            <td data-label="Ko'rsatkich">Buyurtmalar (yangi / bajarildi / jami)</td>
                            <td class="mono" data-label="Hafta">{{ $periods['hafta']['buyurtma_yangi'] }} / {{ $periods['hafta']['buyurtma_bajar'] }} / {{ $periods['hafta']['buyurtma_soni'] }}</td>
                            <td class="mono" data-label="Oy">{{ $periods['oy']['buyurtma_yangi'] }} / {{ $periods['oy']['buyurtma_bajar'] }} / {{ $periods['oy']['buyurtma_soni'] }}</td>
                            <td class="mono" data-label="Yil">{{ $periods['yil']['buyurtma_yangi'] }} / {{ $periods['yil']['buyurtma_bajar'] }} / {{ $periods['yil']['buyurtma_soni'] }}</td>
                        </tr>
                        <tr>
                            <td data-label="Ko'rsatkich">Zarar (chiqim + yo'qotish + brak)</td>
                            <td class="mono" data-label="Hafta">{{ number_format($periods['hafta']['zarar'], 0, '.', ' ') }}</td>
                            <td class="mono" data-label="Oy">{{ number_format($periods['oy']['zarar'], 0, '.', ' ') }}</td>
                            <td class="mono" data-label="Yil">{{ number_format($periods['yil']['zarar'], 0, '.', ' ') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid-2col">

            {{-- HAFTALIK GRAFIK --}}
            <div class="card">
                <div class="card__body">
                    <div class="card__head">
                        <h2>So'nggi 7 kun</h2>
                        <span class="card__meta">sotuv + buyurtma</span>
                    </div>
                    <div class="chart" id="erka-chart"></div>
                    <p class="rank-item__sub" style="margin-top:8px;">Har kun uchun sotuv va buyurtma yozuvlari yig'indisi.</p>
                </div>
            </div>

            {{-- TOP MAHSULOTLAR --}}
            <div class="card">
                <div class="card__body">
                    <div class="card__head">
                        <h2>Top mahsulotlar</h2>
                        <span class="card__meta">sotuv bo'yicha</span>
                    </div>
                    <div class="rank-list">
                        @forelse ($topProducts as $i => $row)
                            <div class="rank-item">
                                <span class="rank-item__num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <div style="flex:1;">
                                    <p class="rank-item__name">{{ $row->product->nomi ?? '—' }}</p>
                                    <p class="rank-item__sub">{{ (int) $row->jami_miqdor }} dona sotildi</p>
                                </div>
                                <span class="rank-item__value mono">{{ number_format((float) $row->jami_summa, 0, '.', ' ') }}</span>
                            </div>
                        @empty
                            <p class="rank-item__sub" style="padding:16px 0;">Hali sotuv yo'q.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- OXIRGI BUYURTMALAR --}}
        <div class="card table-card">
            <div class="card__head">
                <h2>Oxirgi buyurtmalar</h2>
                @if ($hasBuyurtma)
                    <a href="{{ route('admin.buyurtmalar.index') }}" class="card__link">Barchasini ko'rish →</a>
                @endif
            </div>
            <div style="overflow-x:auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sana</th>
                            <th>Mijoz</th>
                            <th>Mahsulot</th>
                            <th>Miqdor</th>
                            <th>Summa</th>
                            <th>Holat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($oxirgiBuyurtmalar as $b)
                            <tr>
                                <td class="mono" data-label="Sana">
                                    @if (isset($b->sana) && $b->sana)
                                        {{ \Illuminate\Support\Carbon::parse($b->sana)->format('d.m.Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td data-label="Mijoz">
                                    @if ($hasBuyurtma)
                                        {{ $b->mijoz_nomi }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td data-label="Mahsulot">
                                    @if ($hasBuyurtma)
                                        {{ $b->nomi }}
                                    @else
                                        {{ $b->product->nomi ?? '—' }}
                                        @if ($b->productRang ?? null)
                                            — {{ $b->productRang->rangi }}
                                        @endif
                                    @endif
                                </td>
                                <td class="mono" data-label="Miqdor">{{ $b->miqdori }}</td>
                                <td class="mono" data-label="Summa">
                                    @if ($hasBuyurtma)
                                        {{ number_format($b->jami_summa, 0, '.', ' ') }}
                                    @else
                                        {{ number_format((float) $b->jami_summa, 0, '.', ' ') }}
                                    @endif
                                </td>
                                <td data-label="Holat">
                                    @php $h = $b->holat ?? 'sotildi'; @endphp
                                    @if ($h === 'yangi' || $h === 'buyurtma')
                                        <span class="badge badge--new">Yangi</span>
                                    @elseif ($h === 'jarayonda')
                                        <span class="badge badge--progress">Jarayonda</span>
                                    @elseif ($h === 'bajarildi' || $h === 'sotildi')
                                        <span class="badge badge--done">Bajarildi</span>
                                    @else
                                        <span class="badge">{{ $h }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center;color:var(--ink-faint);padding:28px 0;">
                                    Buyurtma yo'q.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- OXIRGI SOTUVLAR --}}
        <div class="card table-card">
            <div class="card__head">
                <h2>Oxirgi sotuvlar</h2>
                <a href="{{ route('admin.products.index') }}" class="card__link">Mahsulotlar →</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sana</th>
                            <th>Mahsulot</th>
                            <th>Miqdor</th>
                            <th>Tushum</th>
                            <th>Foyda</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($oxirgiSotuvlar as $s)
                            <tr>
                                <td class="mono" data-label="Sana">
                                    {{ \Illuminate\Support\Carbon::parse($s->sana)->format('d.m.Y') }}
                                </td>
                                <td data-label="Mahsulot">
                                    {{ $s->product->nomi ?? '—' }}
                                    @if ($s->productRang)
                                        — {{ $s->productRang->rangi }}
                                    @endif
                                </td>
                                <td class="mono" data-label="Miqdor">{{ $s->miqdori }}</td>
                                <td class="mono" data-label="Tushum">{{ number_format((float) $s->jami_summa, 0, '.', ' ') }}</td>
                                <td data-label="Foyda">
                                    <span class="badge {{ $s->foyda >= 0 ? 'badge--done' : 'badge--progress' }}">
                                        {{ $s->foyda >= 0 ? '+' : '' }}{{ number_format($s->foyda, 0, '.', ' ') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;color:var(--ink-faint);padding:28px 0;">
                                    Sotuv yo'q.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        (function () {
            var weekly = @json($weeklyChart);
            var max = 1;
            weekly.forEach(function (d) {
                if (d.value > max) max = d.value;
            });
            var chart = document.getElementById('erka-chart');
            if (!chart) return;

            weekly.forEach(function (day, i) {
                var col = document.createElement('div');
                col.className = 'chart__col';
                var isPeak = day.value === max && max > 0;
                col.innerHTML =
                    '<span class="chart__value mono">' + day.value + '</span>' +
                    '<div class="chart__track"><div class="chart__fill' + (isPeak ? ' is-peak' : '') + '"></div></div>' +
                    '<span class="chart__day">' + day.label + '</span>';
                chart.appendChild(col);
                var fill = col.querySelector('.chart__fill');
                setTimeout(function () {
                    fill.style.height = (day.value / max * 100) + '%';
                }, 40 + i * 30);
            });
        })();
    </script>

</x-layouts.sidebar>
