<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SaaSToko — Kelola Toko Anda</title>
    <meta name="description" content="Platform manajemen toko modular: kasir, stok, karyawan, dan laporan keuangan dalam satu sistem.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
    </style>
</head>
<body class="bg-white text-slate-800 antialiased">

    <!-- Nav -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5">
                <div class="h-8 w-8 rounded-lg bg-slate-900 flex items-center justify-center">
                    <svg class="h-4.5 w-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <span class="font-bold text-lg text-slate-900">SaaSToko</span>
            </a>

            <nav class="hidden md:flex items-center gap-8 text-sm text-slate-600">
                <a href="#fitur" class="hover:text-slate-900 transition">Fitur</a>
                <a href="#harga" class="hover:text-slate-900 transition">Harga</a>
                <a href="#faq" class="hover:text-slate-900 transition">FAQ</a>
            </nav>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ auth()->user()->isSuperadmin() ? route('superadmin.dashboard') : route('dashboard') }}"
                       class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800 transition">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800 transition">
                        Masuk
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 pt-16 sm:pt-24 pb-16">
        <div class="max-w-2xl">
            <p class="text-sm font-medium text-brand-600 mb-3">Platform manajemen toko modular</p>
            <h1 class="text-3xl sm:text-5xl font-bold text-slate-900 leading-tight tracking-tight">
                Satu sistem untuk<br>seluruh operasional toko Anda.
            </h1>
            <p class="mt-4 text-lg text-slate-500 leading-relaxed max-w-xl">
                Mulai dari pencatatan kas sederhana. Tambahkan kasir POS, gudang, absensi, atau payroll kapan saja — tanpa ganti sistem.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('login') }}" class="px-6 py-3 rounded-lg bg-slate-900 text-white font-semibold text-sm hover:bg-slate-800 transition">
                    Coba Demo →
                </a>
                <a href="#fitur" class="px-6 py-3 rounded-lg bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition">
                    Lihat Fitur
                </a>
            </div>
        </div>
    </section>

    <!-- Screenshot preview -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 pb-20">
        <div class="rounded-xl border border-slate-200 bg-slate-50 overflow-hidden shadow-sm">
            <div class="bg-slate-100 px-4 py-2.5 border-b border-slate-200 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                <span class="ml-4 text-xs text-slate-400 font-mono">saastoko.test/kasir</span>
            </div>
            <div class="p-6 sm:p-8 grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Katalog -->
                <div class="lg:col-span-2">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-900">Katalog Produk</h3>
                        <span class="text-xs text-slate-400">Etalase Utama</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach([
                            ['sku' => 'KOPI-01', 'nama' => 'Espresso Blend 1kg', 'harga' => '130.000', 'stok' => 45],
                            ['sku' => 'TEA-02', 'nama' => 'Matcha Latte Mix', 'harga' => '28.000', 'stok' => 20],
                            ['sku' => 'SNK-05', 'nama' => 'Croissant Butter', 'harga' => '22.000', 'stok' => 12],
                        ] as $item)
                        <div class="p-3 rounded-lg border border-slate-200 bg-white">
                            <p class="text-[11px] text-slate-400 font-mono">{{ $item['sku'] }}</p>
                            <p class="font-medium text-slate-800 text-sm mt-0.5">{{ $item['nama'] }}</p>
                            <div class="flex justify-between items-center mt-2 text-xs">
                                <span class="font-semibold text-slate-900">Rp {{ $item['harga'] }}</span>
                                <span class="text-slate-400">Stok {{ $item['stok'] }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Keranjang -->
                <div class="p-4 rounded-lg border border-slate-200 bg-white flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <h4 class="font-semibold text-slate-900 text-sm">Keranjang</h4>
                            <span class="text-xs text-slate-400">2 item</span>
                        </div>
                        <div class="py-3 space-y-2 text-sm">
                            <div class="flex justify-between text-slate-600">
                                <span>Espresso Blend ×2</span>
                                <span class="text-slate-900 font-medium">Rp 260.000</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Croissant ×1</span>
                                <span class="text-slate-900 font-medium">Rp 22.000</span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-3 border-t border-slate-100">
                        <div class="flex justify-between font-semibold text-sm mb-3">
                            <span>Total</span>
                            <span>Rp 282.000</span>
                        </div>
                        <div class="w-full py-2 rounded-lg bg-slate-900 text-white font-medium text-xs text-center">
                            Bayar — Tunai
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Fitur -->
    <section id="fitur" class="border-t border-slate-200 bg-slate-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-20">
            <p class="text-sm font-medium text-brand-600 mb-2">Apa saja yang bisa dilakukan</p>
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">Modul yang bisa Anda aktifkan</h2>
            <p class="text-slate-500 mt-2 max-w-xl">Tiap modul bekerja sendiri-sendiri. Aktifkan yang dibutuhkan, matikan yang belum perlu.</p>

            <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach([
                    ['judul' => 'Pencatatan Kas & Pengeluaran', 'desc' => 'Catat penjualan harian dan pengeluaran operasional. Lihat rekap laba rugi per bulan.'],
                    ['judul' => 'Kasir POS', 'desc' => 'Cari produk, tambah ke keranjang, hitung kembalian. Cetak struk. Stok berkurang otomatis.'],
                    ['judul' => 'Stok & Multi-Gudang', 'desc' => 'Kelola stok di beberapa lokasi. Transfer antar gudang, catat barang masuk dari supplier.'],
                    ['judul' => 'Alert Stok & Opname', 'desc' => 'Notifikasi produk yang hampir habis. Lakukan stok opname fisik dan lihat selisihnya.'],
                    ['judul' => 'Absensi GPS Karyawan', 'desc' => 'Karyawan presensi dari HP. Sistem cek apakah mereka berada dalam radius toko.'],
                    ['judul' => 'Penggajian & Slip', 'desc' => 'Hitung gaji otomatis dari kehadiran. Cetak slip gaji per periode.'],
                ] as $fitur)
                <div class="p-5 rounded-xl bg-white border border-slate-200">
                    <h3 class="font-semibold text-slate-900 text-base">{{ $fitur['judul'] }}</h3>
                    <p class="text-sm text-slate-500 mt-1.5 leading-relaxed">{{ $fitur['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Harga -->
    <section id="harga" class="border-t border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-20">
            <p class="text-sm font-medium text-brand-600 mb-2">Paket berlangganan</p>
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">Pilih sesuai kebutuhan, upgrade kapan saja</h2>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Basic -->
                <div class="p-6 rounded-xl border border-slate-200 bg-white flex flex-col">
                    <div>
                        <h3 class="font-bold text-slate-900 text-lg">Basic</h3>
                        <p class="text-sm text-slate-500 mt-1">Pencatatan kas sederhana.</p>
                        <p class="mt-4"><span class="text-3xl font-bold text-slate-900">Rp 99rb</span><span class="text-slate-400 text-sm"> / bln</span></p>
                        <ul class="mt-6 space-y-2.5 text-sm text-slate-600">
                            <li class="flex gap-2"><span class="text-slate-400">—</span> Master Produk & Kategori</li>
                            <li class="flex gap-2"><span class="text-slate-400">—</span> Penjualan Sederhana</li>
                            <li class="flex gap-2"><span class="text-slate-400">—</span> Pencatatan Pengeluaran</li>
                            <li class="flex gap-2"><span class="text-slate-400">—</span> Rekap Laba Rugi</li>
                        </ul>
                    </div>
                    <a href="{{ route('login') }}" class="mt-8 w-full py-2.5 rounded-lg border border-slate-300 text-slate-700 font-medium text-sm text-center hover:bg-slate-50 transition">
                        Mulai
                    </a>
                </div>

                <!-- Pro -->
                <div class="p-6 rounded-xl border-2 border-slate-900 bg-white flex flex-col relative">
                    <span class="absolute -top-3 left-5 px-3 py-0.5 bg-slate-900 text-white text-xs font-semibold rounded-full">Populer</span>
                    <div>
                        <h3 class="font-bold text-slate-900 text-lg">Pro</h3>
                        <p class="text-sm text-slate-500 mt-1">Kasir POS dan kontrol stok.</p>
                        <p class="mt-4"><span class="text-3xl font-bold text-slate-900">Rp 199rb</span><span class="text-slate-400 text-sm"> / bln</span></p>
                        <ul class="mt-6 space-y-2.5 text-sm text-slate-600">
                            <li class="flex gap-2"><span class="text-slate-400">—</span> Semua fitur Basic</li>
                            <li class="flex gap-2"><span class="text-slate-400">—</span> Kasir POS</li>
                            <li class="flex gap-2"><span class="text-slate-400">—</span> Stok Gudang Otomatis</li>
                            <li class="flex gap-2"><span class="text-slate-400">—</span> Alert Stok & Opname</li>
                            <li class="flex gap-2"><span class="text-slate-400">—</span> Laporan HPP</li>
                        </ul>
                    </div>
                    <a href="{{ route('login') }}" class="mt-8 w-full py-2.5 rounded-lg bg-slate-900 text-white font-medium text-sm text-center hover:bg-slate-800 transition">
                        Mulai
                    </a>
                </div>

                <!-- Enterprise -->
                <div class="p-6 rounded-xl border border-slate-200 bg-white flex flex-col">
                    <div>
                        <h3 class="font-bold text-slate-900 text-lg">Enterprise</h3>
                        <p class="text-sm text-slate-500 mt-1">Multi-gudang dan rantai pasok.</p>
                        <p class="mt-4"><span class="text-3xl font-bold text-slate-900">Rp 399rb</span><span class="text-slate-400 text-sm"> / bln</span></p>
                        <ul class="mt-6 space-y-2.5 text-sm text-slate-600">
                            <li class="flex gap-2"><span class="text-slate-400">—</span> Semua fitur Pro</li>
                            <li class="flex gap-2"><span class="text-slate-400">—</span> Multi-Gudang & Transfer</li>
                            <li class="flex gap-2"><span class="text-slate-400">—</span> Penerimaan Barang Masuk</li>
                            <li class="flex gap-2"><span class="text-slate-400">—</span> Kartu Stok Mutasi</li>
                            <li class="flex gap-2"><span class="text-slate-400">—</span> Add-on Karyawan & Payroll</li>
                        </ul>
                    </div>
                    <a href="{{ route('login') }}" class="mt-8 w-full py-2.5 rounded-lg border border-slate-300 text-slate-700 font-medium text-sm text-center hover:bg-slate-50 transition">
                        Mulai
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="border-t border-slate-200 bg-slate-50">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-20">
            <h2 class="text-2xl font-bold text-slate-900 mb-8">Pertanyaan umum</h2>
            <div class="space-y-6">
                <div>
                    <h3 class="font-semibold text-slate-900">Bisa tambah modul tanpa upgrade paket?</h3>
                    <p class="text-sm text-slate-500 mt-1">Bisa. Anda bisa beli modul tambahan (misal Absensi atau Payroll) secara terpisah lewat menu Tagihan.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Bagaimana validasi absensi GPS?</h3>
                    <p class="text-sm text-slate-500 mt-1">Karyawan membuka halaman absensi dari HP. Sistem membaca koordinat dan menghitung jaraknya ke toko. Jika masih dalam radius yang ditentukan, absensi diterima.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Apakah data antar toko aman?</h3>
                    <p class="text-sm text-slate-500 mt-1">Ya. Setiap toko hanya bisa mengakses datanya sendiri. Sistem mengisolasi data di level database query secara otomatis.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Siapa yang mendaftarkan toko baru?</h3>
                    <p class="text-sm text-slate-500 mt-1">Pendaftaran toko dilakukan oleh Superadmin platform. Setelah terdaftar, admin toko langsung bisa login dan mulai menggunakan sistem.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-slate-200 py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-slate-400">
            <span>© {{ date('Y') }} SaaSToko</span>
            <div class="flex gap-6">
                <a href="#fitur" class="hover:text-slate-600 transition">Fitur</a>
                <a href="#harga" class="hover:text-slate-600 transition">Harga</a>
                <a href="{{ route('login') }}" class="hover:text-slate-600 transition">Masuk</a>
            </div>
        </div>
    </footer>

</body>
</html>
