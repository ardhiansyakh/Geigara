<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Monitoring Radiasi Ruang Tunggu Radiologi - GEIGARA</title>
    <!-- Tailwind CSS CDN -->x
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .mono-font {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-800">

    <!-- HEADER / NAVBAR -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between py-3.5 gap-4">

                <!-- Judul & Status ESP32 -->
                <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-start">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xl shadow-sm">
                            <i class="fa-solid fa-radiation"></i>
                        </div>
                        <div>
                            <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">
                                Sistem Monitoring Radiasi Ruang Tunggu Radiologi
                            </h1>
                            <p class="text-xs text-slate-500 flex items-center gap-2">
                                <span>Sensor Geiger-Müller (ESP32)</span>
                                <span class="text-slate-300">•</span>
                                <span id="esp-status-indicator" class="inline-flex items-center gap-1 font-medium text-slate-500">
                                    <span id="esp-dot" class="w-2 h-2 rounded-full bg-slate-400"></span>
                                    <span id="esp-status-text">Alat ESP32 Belum Terhubung</span>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Jam Digital Real-Time & Profil Pengguna -->
                <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">

                    <!-- Widget Jam -->
                    <div class="bg-slate-100 border border-slate-200 rounded-xl px-4 py-1.5 text-right">
                        <div id="live-date" class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">
                            Memuat Tanggal...
                        </div>
                        <div class="mono-font text-lg font-bold text-slate-800 flex items-center justify-end gap-1">
                            <span id="clock-hours">00</span>
                            <span class="text-slate-400">:</span>
                            <span id="clock-minutes">00</span>
                            <span class="text-slate-400">:</span>
                            <span id="clock-seconds" class="text-emerald-600">00</span>
                            <span class="text-xs font-sans font-medium text-slate-500 ml-1">WIB</span>
                        </div>
                    </div>

                    <!-- User Profil & Tombol Logout -->
                    <div class="flex items-center gap-2.5 pl-2 border-l border-slate-200">
                        <div class="hidden md:block text-right">
                            <div class="text-xs font-bold text-slate-800 flex items-center justify-end gap-1.5">
                                <span>{{ Auth::user()->user ?? Auth::user()->username ?? 'Admin' }}</span>
                                @if(Auth::user()->isAdmin())
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase bg-rose-100 text-rose-700 border border-rose-200 flex items-center gap-0.5">
                                        <i class="fa-solid fa-crown text-[8px] text-rose-500"></i> Admin
                                    </span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-slate-100 text-slate-600 border border-slate-200">
                                        User Biasa
                                    </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-emerald-600 font-medium flex items-center justify-end gap-1 mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                <span>&#64;{{ Auth::user()->user ?? Auth::user()->username ?? 'admin' }}</span>
                                @if(!Auth::user()->isAdmin())
                                    <span class="text-[10px] text-slate-400 font-normal italic">(Lihat Saja)</span>
                                @endif
                            </div>
                        </div>

                        @if(Auth::user()->isAdmin())
                            <!-- Tombol Kelola User (Khusus Admin) -->
                            <button onclick="openUserModal()" title="Kelola Pengguna & Jadikan Admin"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-xl transition shadow-sm">
                                <i class="fa-solid fa-users-gear"></i>
                                <span class="hidden sm:inline">Kelola User</span>
                            </button>
                        @endif

                        <!-- Tombol Keluar -->
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" title="Keluar dari akun"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-rose-50 hover:text-rose-600 border border-slate-200 hover:border-rose-200 rounded-xl transition shadow-sm">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                <span class="hidden sm:inline">Keluar</span>
                            </button>
                        </form>
                    </div>

                </div>

            </div>
        </div>
    </header>

    <!-- KONTEN UTAMA DASHBOARD -->
    <main class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 flex-1">

        <!-- Flash Pesan Sukses -->
        @if (session('success'))
            <div id="flash-alert"
                class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="document.getElementById('flash-alert').style.display='none'"
                    class="text-emerald-600 hover:text-emerald-800 text-xs font-bold">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        <!-- Flash Pesan Error / Akses Ditolak -->
        @if (session('error'))
            <div id="flash-error"
                class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="document.getElementById('flash-error').style.display='none'"
                    class="text-rose-600 hover:text-rose-800 text-xs font-bold">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        <!-- BANNER PERINGATAN (HANYA MUNCUL JIKA WASPADA ATAU BERBAHAYA) -->
        <div id="status-alert-banner" class="hidden rounded-2xl p-4 sm:p-5 border items-center justify-between gap-4 transition-all">
            <div class="flex items-center gap-3.5">
                <div id="banner-icon-bg" class="w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0">
                    <i id="banner-icon" class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 id="banner-title" class="font-bold text-sm sm:text-base"></h3>
                    <p id="banner-desc" class="text-xs sm:text-sm mt-0.5"></p>
                </div>
            </div>
            <div class="shrink-0">
                <span id="banner-badge" class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider"></span>
            </div>
        </div>

        <!-- 4 KARTU INFORMASI UTAMA -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

            <!-- KARTU 1: STATUS RADIASI RUANG TUNGGU -->
            <div id="card-status-box" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide flex items-center gap-1.5">
                        <i class="fa-solid fa-shield-halved text-slate-400"></i> Status Radiasi Ruang Tunggu
                    </span>
                    <div class="mt-3">
                        <span id="status-badge" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-base font-extrabold uppercase bg-slate-100 text-slate-600 border border-slate-300">
                            <i id="status-icon" class="fa-solid fa-circle-pause text-slate-400"></i>
                            <span id="status-text">MENUNGGU ALAT</span>
                        </span>
                    </div>
                    <p id="status-desc" class="text-xs text-slate-500 mt-3 leading-relaxed">
                        Alat ESP32 belum terhubung. Menunggu data radiasi pertama masuk...
                    </p>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Ambang Batas Normal:</span>
                    <span class="font-semibold text-slate-700">&lt; 50 CPM (Aman)</span>
                </div>
            </div>

            <!-- KARTU 2: TINGKAT RADIASI (SATUAN UTAMA: CPM) -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide flex items-center gap-1.5">
                            <i class="fa-solid fa-radiation text-emerald-600"></i> Laju Radiasi
                        </span>
                        <span class="text-[11px] font-mono text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                            Geiger GM
                        </span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span id="cpm-value" class="mono-font text-3xl sm:text-4xl font-extrabold text-slate-400">
                            --
                        </span>
                        <span class="text-sm font-bold text-slate-500">CPM</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                        <span>Konversi Dosis:</span>
                        <span class="font-bold text-slate-600 font-mono"><span id="radiation-value">--</span> µSv/h</span>
                    </div>
                </div>

                <!-- Progress Bar Radiasi CPM -->
                <div class="mt-4">
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div id="radiation-progress" class="bg-slate-300 h-full transition-all duration-300" style="width: 0%;"></div>
                    </div>
                    <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                        <span>0</span>
                        <span>50 (Aman)</span>
                        <span>150 (Waspada)</span>
                        <span>300+ CPM</span>
                    </div>
                </div>
            </div>

            <!-- KARTU 3: SUHU LINGKUNGAN RUANGAN -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide flex items-center gap-1.5">
                        <i class="fa-solid fa-temperature-half text-amber-500"></i> Suhu Ruangan
                    </span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span id="temp-value" class="mono-font text-3xl sm:text-4xl font-extrabold text-slate-400">
                            --
                        </span>
                        <span class="text-sm font-bold text-slate-500">°C</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                        <span>Konversi:</span>
                        <span class="font-bold text-slate-600 font-mono"><span id="temp-f-value">--</span> °F</span>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Status Suhu:</span>
                    <span id="temp-status-text" class="font-semibold text-slate-400">Menunggu Alat</span>
                </div>
            </div>

            <!-- KARTU 4: KELEMBAPAN UDARA -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide flex items-center gap-1.5">
                        <i class="fa-solid fa-droplet text-blue-500"></i> Kelembapan Udara
                    </span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span id="humidity-value" class="mono-font text-3xl sm:text-4xl font-extrabold text-slate-400">
                            --
                        </span>
                        <span class="text-sm font-bold text-slate-500">% RH</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                        <span>Kondisi Udara:</span>
                        <span id="humidity-status-text" class="font-bold text-slate-400">Menunggu Alat</span>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Rentang Ideal:</span>
                    <span class="font-semibold text-slate-600">45% - 70%</span>
                </div>
            </div>

        </div>

        <!-- GRAFIK REAL-TIME PEMANTAUAN RADIASI (CPM) & SUHU -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-sm">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-chart-line text-emerald-600"></i>
                        Grafik Pemantauan Radiasi Real-Time
                    </h2>
                    <p class="text-xs text-slate-500">
                        Riwayat laju radiasi (CPM) dan temperatur ruangan (°C) yang dikirim oleh ESP32
                    </p>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-600">
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-1 bg-emerald-500 rounded"></span> Radiasi (CPM)
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-1 bg-amber-500 rounded"></span> Suhu (°C)
                    </span>
                </div>
            </div>

            <!-- Canvas Grafik -->
            <div class="w-full h-72 sm:h-80 relative">
                <canvas id="radiationChart"></canvas>
            </div>
        </div>

        <!-- TABEL LOG DATA SENSOR REAL-TIME -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-table-list text-slate-600"></i>
                            Log Data Sensor Real-Time (MySQL Laragon)
                        </h2>
                        <p class="text-xs text-slate-500">Tersimpan otomatis saat ESP32 mengirim data radiasi</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(Auth::user()->isAdmin())
                            <button onclick="clearAllData()" title="Kosongkan seluruh data di database (Aksi Administrator)"
                                class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-1.5 transition shadow-sm">
                                <i class="fa-solid fa-trash-can"></i> Kosongkan Data
                            </button>
                        @else
                            <span title="Hanya Administrator yang dapat mengosongkan riwayat data"
                                class="px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-200 text-slate-400 text-xs font-semibold flex items-center gap-1.5 cursor-not-allowed select-none">
                                <i class="fa-solid fa-lock text-[10px]"></i> Mode Lihat Saja
                            </span>
                        @endif
                        <button onclick="exportToCSV()"
                            class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition">
                            <i class="fa-solid fa-download"></i> Ekspor CSV
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-72">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase border-y border-slate-200 font-semibold text-[10px]">
                            <tr>
                                <th class="py-2.5 px-3">Waktu</th>
                                <th class="py-2.5 px-3">Radiasi (CPM)</th>
                                <th class="py-2.5 px-3">Dosis (µSv/h)</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3">Suhu</th>
                                <th class="py-2.5 px-3">Kelembapan</th>
                            </tr>
                        </thead>
                        <tbody id="telemetry-table-body" class="divide-y divide-slate-100 text-slate-700 font-mono">
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 font-sans text-xs">
                                    <i class="fa-solid fa-inbox text-slate-300 text-2xl block mb-2"></i>
                                    Belum ada data dari ESP32. Hubungkan alat untuk mulai menerima data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Terakhir diperbarui: <span id="last-update-time" class="font-bold text-slate-700">Belum ada data</span></span>
                <span>Interval Cek: <strong class="text-slate-700">1.5 Detik</strong></span>
            </div>
        </div>

    </main>

    <!-- FOOTER -->
    <footer class="bg-white border-t border-slate-200 py-3.5 px-4 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>
                © 2026 THE INOTS - Sistem Monitoring Radiasi Pada Ruang Tunggu Radiologi
            </div>
            <div class="text-slate-400">
                Sensor Geiger-Müller (ESP32) • Satuan Counts Per Minute (CPM) • MySQL Laragon
            </div>
        </div>
    </footer>

    <!-- JAVASCRIPT REAL-TIME LOGIC -->
    <script>
        // State Global Aplikasi
        const state = {
            chart: null,
            chartData: { labels: [], cpm: [], temp: [] },
            historyRecords: [],
            lastId: 0,
        };

        // 1. UPDATE JAM DIGITAL SETIAP DETIK
        function updateClock() {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            document.getElementById('live-date').innerText = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;

            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');

            document.getElementById('clock-hours').innerText = h;
            document.getElementById('clock-minutes').innerText = m;
            document.getElementById('clock-seconds').innerText = s;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // 2. INISIALISASI GRAFIK REAL-TIME (SATUAN CPM & SUHU)
        function initChart() {
            const ctx = document.getElementById('radiationChart').getContext('2d');
            state.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Laju Radiasi (CPM)',
                            data: [],
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.12)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            yAxisID: 'yCpm'
                        },
                        {
                            label: 'Suhu Ruangan (°C)',
                            data: [],
                            borderColor: '#f59e0b',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            borderDash: [4, 4],
                            tension: 0.35,
                            pointRadius: 2,
                            pointHoverRadius: 5,
                            yAxisID: 'yTemp'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        x: {
                            grid: { color: 'rgba(0, 0, 0, 0.04)' },
                            ticks: { font: { size: 10 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 10 }
                        },
                        yCpm: {
                            type: 'linear',
                            position: 'left',
                            suggestedMin: 0,
                            suggestedMax: 100,
                            grid: { color: 'rgba(0, 0, 0, 0.05)' },
                            ticks: { font: { size: 10 }, callback: val => val + ' CPM' }
                        },
                        yTemp: {
                            type: 'linear',
                            position: 'right',
                            suggestedMin: 20,
                            suggestedMax: 40,
                            grid: { drawOnChartArea: false },
                            ticks: { font: { size: 10 }, callback: val => val + '°C' }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { boxWidth: 12, font: { size: 11 } }
                        }
                    }
                }
            });
        }

        // 3. UPDATE TAMPILAN STATUS (AMAN, WASPADA, BERBAHAYA)
        function updateStatusUI(status, cpm, radiation) {
            const badge = document.getElementById('status-badge');
            const icon = document.getElementById('status-icon');
            const text = document.getElementById('status-text');
            const desc = document.getElementById('status-desc');
            const cardBox = document.getElementById('card-status-box');
            const banner = document.getElementById('status-alert-banner');
            const bannerIconBg = document.getElementById('banner-icon-bg');
            const bannerIcon = document.getElementById('banner-icon');
            const bannerTitle = document.getElementById('banner-title');
            const bannerDesc = document.getElementById('banner-desc');
            const bannerBadge = document.getElementById('banner-badge');
            const radProgress = document.getElementById('radiation-progress');

            const statusUpper = (status || 'AMAN').toUpperCase();

            badge.className = 'inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-base font-extrabold uppercase border ';
            cardBox.className = 'bg-white rounded-2xl p-5 border shadow-sm flex flex-col justify-between ';

            if (statusUpper === 'AMAN') {
                badge.className += 'bg-emerald-100 text-emerald-800 border-emerald-300';
                cardBox.className += 'border-emerald-200';
                icon.className = 'fa-solid fa-circle-check text-emerald-600';
                text.innerText = 'AMAN';
                desc.innerText = 'Tingkat radiasi latar berada dalam batas normal dan aman bagi pasien & pengunjung (< 50 CPM).';
                radProgress.className = 'bg-emerald-500 h-full transition-all duration-300';
                banner.classList.add('hidden');
                banner.classList.remove('flex');

            } else if (statusUpper === 'WASPADA' || statusUpper === 'SEDANG') {
                badge.className += 'bg-amber-100 text-amber-800 border-amber-300';
                cardBox.className += 'border-amber-300';
                icon.className = 'fa-solid fa-triangle-exclamation text-amber-600';
                text.innerText = 'WASPADA';
                desc.innerText = 'Peringatan: Tingkat radiasi terdeteksi meningkat (50 - 150 CPM). Pantau kebocoran ruang penyinaran.';
                radProgress.className = 'bg-amber-500 h-full transition-all duration-300';

                banner.classList.remove('hidden');
                banner.classList.add('flex', 'bg-amber-50', 'border-amber-200', 'text-amber-900');
                banner.classList.remove('bg-rose-50', 'border-rose-200', 'text-rose-900');
                bannerIconBg.className = 'w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0 bg-amber-200 text-amber-800';
                bannerIcon.className = 'fa-solid fa-triangle-exclamation';
                bannerTitle.innerText = 'PERINGATAN: STATUS RADIASI WASPADA';
                bannerDesc.innerText = `Laju radiasi terdeteksi ${cpm} CPM (${radiation.toFixed(2)} µSv/h) melebihi batas latar normal 50 CPM.`;
                bannerBadge.className = 'px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider bg-amber-200 text-amber-900';
                bannerBadge.innerText = 'WASPADA';

            } else {
                badge.className += 'bg-rose-100 text-rose-800 border-rose-300 animate-pulse';
                cardBox.className += 'border-rose-400';
                icon.className = 'fa-solid fa-radiation text-rose-600 animate-spin';
                text.innerText = 'BERBAHAYA';
                desc.innerText = 'BAHAYA RADIASI TINGGI (> 150 CPM)! Segera lakukan protokol evakuasi dan isolasi ruang tunggu!';
                radProgress.className = 'bg-rose-600 h-full transition-all duration-300';

                banner.classList.remove('hidden');
                banner.classList.add('flex', 'bg-rose-50', 'border-rose-300', 'text-rose-950');
                banner.classList.remove('bg-amber-50', 'border-amber-200', 'text-amber-900');
                bannerIconBg.className = 'w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0 bg-rose-200 text-rose-700';
                bannerIcon.className = 'fa-solid fa-radiation';
                bannerTitle.innerText = 'BAHAYA RADIASI TINGGI TERDETEKSI!';
                bannerDesc.innerText = `Laju radiasi mencapai ${cpm} CPM (${radiation.toFixed(2)} µSv/h). Harap gunakan baju pelindung timbal dan evakuasi!`;
                bannerBadge.className = 'px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider bg-rose-600 text-white';
                bannerBadge.innerText = 'BERBAHAYA';
            }

            // Progress bar max 300 CPM
            const pct = Math.min(100, Math.max(3, (cpm / 300) * 100));
            radProgress.style.width = `${pct}%`;
        }

        // 4. RESET TAMPILAN KE KONDISI KOSONG (STANDBY / BELUM TERHUBUNG)
        function setEmptyState() {
            const espDot = document.getElementById('esp-dot');
            const espText = document.getElementById('esp-status-text');
            espDot.className = 'w-2 h-2 rounded-full bg-slate-400';
            espText.className = 'text-slate-500 font-normal';
            espText.innerText = 'Alat ESP32 Belum Terhubung (Menunggu Data...)';

            const badge = document.getElementById('status-badge');
            const icon = document.getElementById('status-icon');
            const text = document.getElementById('status-text');
            const desc = document.getElementById('status-desc');
            const cardBox = document.getElementById('card-status-box');
            const banner = document.getElementById('status-alert-banner');
            const radProgress = document.getElementById('radiation-progress');

            badge.className = 'inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-base font-extrabold uppercase bg-slate-100 text-slate-600 border border-slate-300';
            cardBox.className = 'bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between';
            icon.className = 'fa-solid fa-circle-pause text-slate-400';
            text.innerText = 'MENUNGGU ALAT';
            desc.innerText = 'Alat ESP32 belum terhubung. Menunggu data radiasi pertama masuk...';
            radProgress.style.width = '0%';
            radProgress.className = 'bg-slate-300 h-full transition-all duration-300';

            if (banner) {
                banner.classList.add('hidden');
                banner.classList.remove('flex');
            }

            document.getElementById('cpm-value').innerText = '--';
            document.getElementById('cpm-value').className = 'mono-font text-3xl sm:text-4xl font-extrabold text-slate-400';
            document.getElementById('radiation-value').innerText = '--';
            document.getElementById('temp-value').innerText = '--';
            document.getElementById('temp-value').className = 'mono-font text-3xl sm:text-4xl font-extrabold text-slate-400';
            document.getElementById('temp-f-value').innerText = '--';
            document.getElementById('temp-status-text').innerText = 'Menunggu Alat';
            document.getElementById('temp-status-text').className = 'font-semibold text-slate-400';
            document.getElementById('humidity-value').innerText = '--';
            document.getElementById('humidity-value').className = 'mono-font text-3xl sm:text-4xl font-extrabold text-slate-400';
            document.getElementById('humidity-status-text').innerText = 'Menunggu Alat';
            document.getElementById('humidity-status-text').className = 'font-bold text-slate-400';
            document.getElementById('last-update-time').innerText = 'Belum ada data';
        }

        // 5. MEMASUKKAN BARIS KE TABEL LOG
        function prependTableRow(item) {
            const tbody = document.getElementById('telemetry-table-body');

            if (tbody.children.length === 1 && tbody.children[0].innerText.includes('Belum ada data')) {
                tbody.innerHTML = '';
            }

            const cpmVal = parseInt(item.cpm || item.raw || 0);
            const radVal = Number(item.radiation || (cpmVal / 153.8)).toFixed(3);
            const statusUpper = (item.status || 'AMAN').toUpperCase();

            let badgeHtml = '';
            if (statusUpper === 'AMAN') {
                badgeHtml = '<span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">AMAN</span>';
            } else if (statusUpper === 'WASPADA' || statusUpper === 'SEDANG') {
                badgeHtml = '<span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">WASPADA</span>';
            } else {
                badgeHtml = '<span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold text-[10px]">BERBAHAYA</span>';
            }

            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 transition-colors';
            tr.innerHTML = `
                <td class="py-2.5 px-3 font-semibold text-slate-900">${item.time}</td>
                <td class="py-2.5 px-3 font-bold ${statusUpper === 'BERBAHAYA' ? 'text-rose-600' : statusUpper === 'WASPADA' || statusUpper === 'SEDANG' ? 'text-amber-600' : 'text-emerald-600'}">${cpmVal} CPM</td>
                <td class="py-2.5 px-3 font-mono text-slate-600">${radVal} µSv/h</td>
                <td class="py-2.5 px-3">${badgeHtml}</td>
                <td class="py-2.5 px-3 text-slate-700">${item.temperature !== null && item.temperature !== undefined ? Number(item.temperature).toFixed(1) + '°C' : '-'}</td>
                <td class="py-2.5 px-3 text-slate-700">${item.humidity !== null && item.humidity !== undefined ? Number(item.humidity).toFixed(1) + '%' : '-'}</td>
            `;

            tbody.insertBefore(tr, tbody.firstChild);

            while (tbody.children.length > 25) {
                tbody.removeChild(tbody.lastChild);
            }
        }

        // 6. FETCH DATA TERBARU DARI DATABASE MYSQL (POLLING REALTIME TIAP 1.5 DETIK)
        async function fetchLatestData() {
            try {
                const response = await fetch('/api/data-sensor/latest');
                if (!response.ok) return;
                const result = await response.json();

                const espDot = document.getElementById('esp-dot');
                const espText = document.getElementById('esp-status-text');

                if (result.has_data && result.data) {
                    if (result.is_online) {
                        espDot.className = 'w-2 h-2 rounded-full bg-emerald-500 animate-ping';
                        espText.className = 'text-emerald-700 font-semibold';
                        espText.innerText = 'ESP32 Online (Live)';
                    } else {
                        espDot.className = 'w-2 h-2 rounded-full bg-amber-500';
                        espText.className = 'text-amber-700 font-medium';
                        espText.innerText = `ESP32 Standby (${result.last_seen_seconds_ago} detik lalu)`;
                    }

                    const data = result.data;
                    const cpmVal = parseInt(data.cpm || data.raw || 0);
                    const radVal = Number(data.radiation || (cpmVal / 153.8));
                    document.getElementById('last-update-time').innerText = data.time;

                    // Update Nilai CPM & Dosis µSv/h
                    const cpmEl = document.getElementById('cpm-value');
                    cpmEl.innerText = cpmVal;
                    cpmEl.className = 'mono-font text-3xl sm:text-4xl font-extrabold text-slate-900';
                    document.getElementById('radiation-value').innerText = radVal.toFixed(3);

                    // Update Status Aman/Waspada/Bahaya
                    updateStatusUI(data.status, cpmVal, radVal);

                    // Update Suhu & Kelembapan
                    if (data.temperature !== null && data.temperature !== undefined) {
                        const temp = Number(data.temperature);
                        const tempEl = document.getElementById('temp-value');
                        tempEl.innerText = temp.toFixed(1);
                        tempEl.className = 'mono-font text-3xl sm:text-4xl font-extrabold text-slate-900';
                        document.getElementById('temp-f-value').innerText = ((temp * 9/5) + 32).toFixed(1);

                        let tempDesc = 'Normal';
                        if (temp > 33) tempDesc = 'Hangat / Panas';
                        else if (temp < 20) tempDesc = 'Sejuk';
                        const tempStatusEl = document.getElementById('temp-status-text');
                        tempStatusEl.innerText = tempDesc;
                        tempStatusEl.className = 'font-semibold text-emerald-600';
                    }

                    if (data.humidity !== null && data.humidity !== undefined) {
                        const hum = Number(data.humidity);
                        const humEl = document.getElementById('humidity-value');
                        humEl.innerText = hum.toFixed(1);
                        humEl.className = 'mono-font text-3xl sm:text-4xl font-extrabold text-slate-900';

                        let humDesc = 'Optimal';
                        if (hum < 40) humDesc = 'Kering';
                        else if (hum > 70) humDesc = 'Lembap Tinggi';
                        const humStatusEl = document.getElementById('humidity-status-text');
                        humStatusEl.innerText = humDesc;
                        humStatusEl.className = 'font-bold text-slate-700';
                    }

                    // Tambahkan data baru ke riwayat & grafik jika ID berbeda
                    if (data.id !== state.lastId) {
                        state.lastId = data.id;
                        prependTableRow(data);
                        state.historyRecords.unshift(data);

                        if (state.chart) {
                            state.chart.data.labels.push(data.time);
                            state.chart.data.datasets[0].data.push(cpmVal);
                            state.chart.data.datasets[1].data.push(data.temperature !== null ? Number(data.temperature) : null);

                            if (state.chart.data.labels.length > 20) {
                                state.chart.data.labels.shift();
                                state.chart.data.datasets[0].data.shift();
                                state.chart.data.datasets[1].data.shift();
                            }
                            state.chart.update();
                        }
                    }

                } else {
                    setEmptyState();
                }

            } catch (err) {
                console.error("Gagal mengambil data sensor:", err);
            }
        }

        // 7. LOAD RIWAYAT DATABASE PERTAMA KALI
        async function loadInitialHistory() {
            try {
                const res = await fetch('/api/data-sensor/history?limit=15');
                if (!res.ok) return;
                const result = await res.json();
                if (result.status === 'success' && result.data && result.data.length > 0) {
                    const tbody = document.getElementById('telemetry-table-body');
                    tbody.innerHTML = '';

                    const labels = [];
                    const cpmData = [];
                    const temps = [];

                    result.data.forEach(item => {
                        const cpmVal = parseInt(item.cpm || item.raw || 0);
                        labels.push(item.time);
                        cpmData.push(cpmVal);
                        temps.push(item.temperature !== null ? Number(item.temperature) : null);
                        prependTableRow(item);
                        state.historyRecords.push(item);
                        state.lastId = Math.max(state.lastId, item.id);
                    });

                    if (state.chart) {
                        state.chart.data.labels = labels;
                        state.chart.data.datasets[0].data = cpmData;
                        state.chart.data.datasets[1].data = temps;
                        state.chart.update();
                    }
                } else {
                    setEmptyState();
                }
            } catch (e) {
                console.error("Gagal load history:", e);
                setEmptyState();
            }
        }

        // 8. FUNGSI KOSONGKAN SELURUH DATA DASHBOARD (ADMIN)
        async function clearAllData() {
            if (!confirm('Apakah Anda yakin ingin mengosongkan seluruh riwayat data radiasi di database?')) return;

            try {
                const res = await fetch('/api/data-sensor/clear', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                state.historyRecords = [];
                state.lastId = 0;

                const tbody = document.getElementById('telemetry-table-body');
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400 font-sans text-xs">
                            <i class="fa-solid fa-inbox text-slate-300 text-2xl block mb-2"></i>
                            Belum ada data dari ESP32. Hubungkan alat untuk mulai menerima data...
                        </td>
                    </tr>
                `;

                if (state.chart) {
                    state.chart.data.labels = [];
                    state.chart.data.datasets[0].data = [];
                    state.chart.data.datasets[1].data = [];
                    state.chart.update();
                }

                setEmptyState();
            } catch (err) {
                console.error("Gagal mengosongkan data:", err);
                window.location.href = '/dashboard?clear=1';
            }
        }

        // 9. EKSPOR DATA KE CSV
        function exportToCSV() {
            if (state.historyRecords.length === 0) {
                alert('Belum ada data untuk diekspor!');
                return;
            }

            let csv = 'Timestamp,Radiasi (CPM),Dosis (uSv/h),Status,Suhu (C),Kelembapan (%)\r\n';
            state.historyRecords.forEach(r => {
                const cpmVal = r.cpm || r.raw || 0;
                const radVal = Number(r.radiation || (cpmVal / 153.8)).toFixed(3);
                csv += `"${r.timestamp ?? r.time}","${cpmVal}","${radVal}","${r.status}","${r.temperature ?? ''}","${r.humidity ?? ''}"\r\n`;
            });

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `data_radiasi_cpm_${Date.now()}.csv`;
            a.click();
            URL.revokeObjectURL(url);
        }

        // Jalankan saat halaman siap
        window.addEventListener('DOMContentLoaded', async () => {
            setEmptyState();
            initChart();
            await loadInitialHistory();
            await fetchLatestData();

            // Interval Real-Time Polling setiap 1.5 detik
            setInterval(fetchLatestData, 1500);
        });

        // ==========================================
        // FITUR MANAJEMEN PENGGUNA & ROLE (ADMIN)
        // ==========================================
        function openUserModal() {
            const modal = document.getElementById('user-modal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                loadUsersList();
            }
        }

        function closeUserModal() {
            const modal = document.getElementById('user-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        async function loadUsersList() {
            const tbody = document.getElementById('user-list-tbody');
            if (!tbody) return;
            tbody.innerHTML = '<tr><td colspan="3" class="py-6 text-center text-slate-400 text-xs"><i class="fa-solid fa-spinner fa-spin mr-1.5 text-indigo-500"></i> Memuat daftar akun...</td></tr>';

            try {
                const res = await fetch('/admin/users');
                const result = await res.json();
                if (result.status === 'success' && result.data) {
                    tbody.innerHTML = '';
                    result.data.forEach(u => {
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-slate-50 transition border-b border-slate-100 last:border-b-0';

                        const roleBadge = u.is_admin ?
                            '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-rose-100 text-rose-700 border border-rose-200"><i class="fa-solid fa-crown text-[8px] text-rose-500"></i>ADMIN</span>' :
                            '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">User Biasa</span>';

                        let actionBtn = '';
                        if (u.is_current) {
                            actionBtn = '<span class="text-[10px] text-indigo-600 font-semibold italic bg-indigo-50 px-2 py-1 rounded-md">Akun Anda</span>';
                        } else if (u.is_admin) {
                            actionBtn = `<button onclick="changeRole('${u.username}', 'user')" class="px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-700 text-[11px] font-semibold transition flex items-center gap-1 ml-auto"><i class="fa-solid fa-arrow-down text-[9px]"></i> Turunkan ke User</button>`;
                        } else {
                            actionBtn = `<button onclick="changeRole('${u.username}', 'admin')" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 text-[11px] font-bold transition flex items-center gap-1 ml-auto shadow-sm"><i class="fa-solid fa-crown text-[9px]"></i> Jadikan Admin</button>`;
                        }

                        tr.innerHTML = `
                            <td class="py-3 px-3 font-bold text-slate-800">
                                ${u.username} ${u.is_current ? '<span class="text-[10px] font-normal text-indigo-600 ml-1">(Login)</span>' : ''}
                            </td>
                            <td class="py-3 px-3">${roleBadge}</td>
                            <td class="py-3 px-3 text-right">${actionBtn}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
            } catch (err) {
                tbody.innerHTML = '<tr><td colspan="3" class="py-4 text-center text-rose-500 text-xs font-semibold">Gagal memuat pengguna. Coba refresh kembali.</td></tr>';
            }
        }

        async function changeRole(username, newRole) {
            const roleName = newRole === 'admin' ? 'ADMINISTRATOR (Bisa Mengatur Seluruh Dashboard)' : 'USER BIASA (Hanya Bisa Melihat)';
            if (!confirm(`Apakah Anda yakin ingin mengubah hak akses pengguna '${username}' menjadi ${roleName}?`)) return;

            try {
                const res = await fetch(`/admin/users/${encodeURIComponent(username)}/role`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ role: newRole })
                });

                const result = await res.json();
                const alertBox = document.getElementById('modal-alert');
                if (result.status === 'success') {
                    alertBox.className = 'px-5 py-2.5 text-xs font-semibold bg-emerald-50 text-emerald-800 border-b border-emerald-100 flex items-center gap-2';
                    alertBox.innerHTML = `<i class="fa-solid fa-circle-check text-emerald-600"></i> ${result.message}`;
                    alertBox.classList.remove('hidden');
                    await loadUsersList();
                } else {
                    alertBox.className = 'px-5 py-2.5 text-xs font-semibold bg-rose-50 text-rose-800 border-b border-rose-100 flex items-center gap-2';
                    alertBox.innerHTML = `<i class="fa-solid fa-circle-exclamation text-rose-600"></i> ${result.message}`;
                    alertBox.classList.remove('hidden');
                }
                setTimeout(() => { if (alertBox) alertBox.classList.add('hidden'); }, 5000);
            } catch (e) {
                alert("Gagal mengubah role pengguna: " + e.message);
            }
        }
    </script>

    @if(Auth::user()->isAdmin())
    <!-- MODAL POPUP MANAJEMEN PENGGUNA (KHUSUS ADMIN) -->
    <div id="user-modal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[85vh] animate-in fade-in duration-200">
            <!-- Modal Header -->
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-sm shadow-sm">
                        <i class="fa-solid fa-users-gear text-base"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-slate-900">Manajemen Pengguna & Role</h3>
                        <p class="text-[11px] text-slate-500">Atur hak akses Administrator dan Pengguna Biasa</p>
                    </div>
                </div>
                <button onclick="closeUserModal()" class="text-slate-400 hover:text-slate-600 text-base p-1.5 rounded-lg hover:bg-slate-100 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Alert Box Pesan Hasil Perubahan di Modal -->
            <div id="modal-alert" class="hidden px-5 py-2.5 text-xs font-medium"></div>

            <!-- Modal Body (Tabel Pengguna) -->
            <div class="p-5 overflow-y-auto flex-1">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">Daftar Akun Pengguna</span>
                    <button onclick="loadUsersList()" class="text-[11px] text-indigo-600 hover:text-indigo-800 flex items-center gap-1 font-semibold transition">
                        <i class="fa-solid fa-arrows-rotate"></i> Muat Ulang
                    </button>
                </div>

                <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 border-b border-slate-200 font-semibold uppercase text-[10px]">
                            <tr>
                                <th class="py-2.5 px-3">Username</th>
                                <th class="py-2.5 px-3">Hak Akses (Role)</th>
                                <th class="py-2.5 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="user-list-tbody" class="divide-y divide-slate-100 font-sans">
                            <tr>
                                <td colspan="3" class="py-6 text-center text-slate-400 text-xs">
                                    <i class="fa-solid fa-spinner fa-spin mr-1.5 text-indigo-500"></i> Memuat daftar akun...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-500 space-y-1.5">
                    <div class="font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-shield-halved text-indigo-600"></i> Penjelasan Hak Akses:
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 leading-relaxed">
                        <li><strong class="text-rose-700">Admin:</strong> Memiliki kontrol penuh, bisa mengosongkan data radiasi, dan dapat menjadikan user biasa sebagai Admin.</li>
                        <li><strong class="text-slate-700">User Biasa:</strong> Hanya dapat melihat data, status radiasi, grafik, dan riwayat log secara real-time (View-Only).</li>
                    </ul>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50 flex justify-end">
                <button onclick="closeUserModal()" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold transition shadow-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    @endif
</body>
</html>
