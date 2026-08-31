<x-layouts.sidebar title="Statistika">

    {{-- SALOMLASHUV PANELI --}}
    <div class="card hero">
        <div class="hero__body">
            <p class="hero__eyebrow">{{ now()->translatedFormat('d-F, l') }}</p>
            <h2>Salom, {{ auth()->user()->toliq_ism ?? 'Admin' }}</h2>
            <p>Bugungi ishlab chiqarish va buyurtmalar bo'yicha qisqacha hisobot.</p>
        </div>
        <div class="hero__figure">Ishlab<br>chiqarish<br>hisoboti</div>
    </div>

    {{-- STATISTIKA KARTOCHKALARI --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-card__top">
                <span class="stat-card__label">Jami mahsulotlar</span>
                <span class="stat-card__trend">+3</span>
            </div>
            <div class="stat-card__value mono">{{ $stats['jami_mahsulotlar'] }}</div>
        </div>

        <div class="stat-card stat-card--rust">
            <div class="stat-card__top">
                <span class="stat-card__label">Jami buyurtmalar</span>
                <span class="stat-card__trend">+12</span>
            </div>
            <div class="stat-card__value mono">{{ $stats['jami_buyurtmalar'] }}</div>
        </div>

        <div class="stat-card stat-card--gold">
            <div class="stat-card__top">
                <span class="stat-card__label">Umumiy foyda</span>
                <span class="stat-card__trend">↑ 8%</span>
            </div>
            <div class="stat-card__value mono">{{ $stats['jami_foyda'] }}</div>
        </div>

        <div class="stat-card stat-card--sage">
            <div class="stat-card__top">
                <span class="stat-card__label">Yangi buyurtmalar</span>
                <span class="stat-card__trend stat-card__trend--warn">Diqqat</span>
            </div>
            <div class="stat-card__value mono">{{ $stats['yangi_buyurtmalar'] }}</div>
        </div>
    </div>

    <div class="grid-2col">

        {{-- HAFTALIK GRAFIK --}}
        <div class="card">
            <div class="card__body">
                <div class="card__head">
                    <h2>Haftalik buyurtmalar</h2>
                    <span class="card__meta">so'nggi 7 kun</span>
                </div>
                <div class="chart" id="erka-chart"></div>
            </div>
        </div>

        {{-- TOP MAHSULOTLAR --}}
        <div class="card">
            <div class="card__body">
                <div class="card__head">
                    <h2>Top mahsulotlar</h2>
                </div>
                <div class="rank-list">
                    <div class="rank-item">
                        <span class="rank-item__num">01</span>
                        <img src="https://placehold.co/100x100/35507A/FFFFFF?text=Kiy">
                        <div>
                            <p class="rank-item__name">Rangli koʻylakcha</p>
                            <p class="rank-item__sub">18 ta sotildi</p>
                        </div>
                        <span class="rank-item__value mono">1 620 000</span>
                    </div>
                    <div class="rank-item">
                        <span class="rank-item__num">02</span>
                        <img src="https://placehold.co/100x100/A2503B/FFFFFF?text=Kiy">
                        <div>
                            <p class="rank-item__name">Chaqaloq kombinezoni</p>
                            <p class="rank-item__sub">14 ta sotildi</p>
                        </div>
                        <span class="rank-item__value mono">1 260 000</span>
                    </div>
                    <div class="rank-item">
                        <span class="rank-item__num">03</span>
                        <img src="https://placehold.co/100x100/74806A/FFFFFF?text=Kiy">
                        <div>
                            <p class="rank-item__name">Sport shim</p>
                            <p class="rank-item__sub">11 ta sotildi</p>
                        </div>
                        <span class="rank-item__value mono">990 000</span>
                    </div>
                    <div class="rank-item">
                        <span class="rank-item__num">04</span>
                        <img src="https://placehold.co/100x100/C9973E/FFFFFF?text=Kiy">
                        <div>
                            <p class="rank-item__name">Yozgi shlyapa</p>
                            <p class="rank-item__sub">9 ta sotildi</p>
                        </div>
                        <span class="rank-item__value mono">540 000</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- OXIRGI BUYURTMALAR --}}
    <div class="card table-card">
        <div class="card__head">
            <h2>Oxirgi buyurtmalar</h2>
            <a href="#" class="card__link">Barchasini ko'rish →</a>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Mijoz</th>
                    <th>Mahsulot</th>
                    <th>Miqdor</th>
                    <th>Summa</th>
                    <th>Holati</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Dilnoza Karimova</td>
                    <td>Rangli koʻylakcha</td>
                    <td class="mono">2 ta</td>
                    <td class="mono">178 000 so'm</td>
                    <td><span class="badge badge--new">Yangi</span></td>
                </tr>
                <tr>
                    <td>Sardor Yusupov</td>
                    <td>Sport shim</td>
                    <td class="mono">1 ta</td>
                    <td class="mono">90 000 so'm</td>
                    <td><span class="badge badge--progress">Jarayonda</span></td>
                </tr>
                <tr>
                    <td>Madina Aliyeva</td>
                    <td>Chaqaloq kombinezoni</td>
                    <td class="mono">3 ta</td>
                    <td class="mono">270 000 so'm</td>
                    <td><span class="badge badge--done">Yakunlangan</span></td>
                </tr>
                <tr>
                    <td>Javlon Tursunov</td>
                    <td>Yozgi shlyapa</td>
                    <td class="mono">1 ta</td>
                    <td class="mono">60 000 so'm</td>
                    <td><span class="badge badge--new">Yangi</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        (function () {
            var weekly = [
                { label: 'Dush', value: 4 },
                { label: 'Sesh', value: 7 },
                { label: 'Chor', value: 5 },
                { label: 'Pay',  value: 9 },
                { label: 'Jum',  value: 12 },
                { label: 'Shan', value: 15 },
                { label: 'Yak',  value: 8 },
            ];
            var max = Math.max.apply(null, weekly.map(function (d) { return d.value; }));
            var chart = document.getElementById('erka-chart');
            if (!chart) return;

            weekly.forEach(function (day, i) {
                var col = document.createElement('div');
                col.className = 'chart__col';
                col.innerHTML =
                    '<span class="chart__value mono">' + day.value + '</span>' +
                    '<div class="chart__track"><div class="chart__fill' + (i === 5 ? ' is-peak' : '') + '"></div></div>' +
                    '<span class="chart__day">' + day.label + '</span>';
                chart.appendChild(col);
                var fill = col.querySelector('.chart__fill');
                setTimeout(function () {
                    fill.style.height = (day.value / max * 100) + '%';
                }, 50);
            });
        })();
    </script>

</x-layouts.sidebar>