@php
    $linkBase = 'group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition';
    $active = 'bg-brand-800 text-white shadow-sm';
    $inactive = 'text-brand-100/80 hover:bg-brand-800/60 hover:text-white';
    $navItem = function (string $route, string $label, string $icon) use ($linkBase, $active, $inactive) {
        $isActive = request()->routeIs($route) || request()->routeIs($route.'.*');
        $cls = $linkBase.' '.($isActive ? $active : $inactive);
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">'.$icon.'</svg>';
        return '<a href="'.route($route).'" class="'.$cls.'">'.$svg.'<span>'.$label.'</span></a>';
    };
    $section = fn (string $title) => '<p class="px-3 pt-5 pb-1 text-[11px] uppercase tracking-wider text-brand-300/70 font-semibold">'.$title.'</p>';
@endphp

@if (auth()->user()?->isSuperadmin())
    {!! $section('Manajemen') !!}
    {!! $navItem('superadmin.dashboard', 'Dashboard', '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>') !!}
    {!! $navItem('superadmin.toko.index', 'Toko', '<path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/>') !!}
    {!! $navItem('superadmin.paket.index', 'Paket', '<path d="M20 7L12 3 4 7v10l8 4 8-4z"/><path d="M4 7l8 4 8-4"/><path d="M12 11v10"/>') !!}
    {!! $navItem('superadmin.addon.index', 'Add-on', '<path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>') !!}
    {!! $navItem('superadmin.verifikasi.index', 'Verifikasi', '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>') !!}
@else
    {!! $section('Utama') !!}
    {!! $navItem('dashboard', 'Dashboard', '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>') !!}
    {!! $navItem('pengeluaran.index', 'Pengeluaran', '<path d="M3 6h18"/><path d="M3 12h18"/><path d="M3 18h12"/>') !!}
    {!! $navItem('penjualan-sederhana.index', 'Penjualan', '<path d="M3 3v18h18"/><path d="M7 14l4-4 3 3 5-6"/>') !!}
    {!! $navItem('rekap.index', 'Rekap', '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h6"/>') !!}

    @if (auth()->user()->toko && auth()->user()->toko->setidaknyaPaket(2))
        {!! $section('Toko & Stok') !!}
        {!! $navItem('produk.index', 'Produk', '<path d="M20 7L12 3 4 7v10l8 4 8-4z"/><path d="M4 7l8 4 8-4"/><path d="M12 11v10"/>') !!}
        {!! $navItem('kasir.index', 'Kasir POS', '<path d="M3 6h18l-1.5 9H4.5z"/><circle cx="8" cy="20" r="1.5"/><circle cx="16" cy="20" r="1.5"/>') !!}
        {!! $navItem('stok-opname.index', 'Stok Opname', '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>') !!}
    @endif

    @if (auth()->user()->toko && auth()->user()->toko->setidaknyaPaket(3))
        {!! $section('Gudang') !!}
        {!! $navItem('gudang.index', 'Gudang', '<path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/>') !!}
        {!! $navItem('barang-masuk.index', 'Barang Masuk', '<path d="M12 5v14"/><path d="M19 12l-7 7-7-7"/>') !!}
        {!! $navItem('transfer-stok.index', 'Transfer Stok', '<path d="M7 16V4"/><path d="M3 8l4-4 4 4"/><path d="M17 8v12"/><path d="M21 16l-4 4-4-4"/>') !!}
        {!! $navItem('kartu-stok.index', 'Kartu Stok', '<path d="M4 4h16v16H4z"/><path d="M4 9h16"/><path d="M9 4v16"/>') !!}
    @endif

    @if (auth()->user()->toko && auth()->user()->toko->punyaAddon('absensi'))
        {!! $section('Kehadiran') !!}
        {!! $navItem('absensi.index', 'Absensi', '<path d="M12 21s-7-4.5-7-10a7 7 0 0 1 14 0c0 5.5-7 10-7 10z"/><circle cx="12" cy="11" r="2.5"/>') !!}
        {!! $navItem('rekap-kehadiran.index', 'Rekap Kehadiran', '<path d="M3 3v18h18"/><path d="M8 17l4-5 3 3 5-7"/>') !!}
    @endif

    @if (auth()->user()->toko && auth()->user()->toko->punyaAddon('penggajian') && auth()->user()->peran === 'admin')
        {!! $section('Payroll') !!}
        {!! $navItem('karyawan.index', 'Karyawan', '<circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/>') !!}
        {!! $navItem('penggajian.index', 'Penggajian', '<path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>') !!}
    @endif

    @if (auth()->user()?->peran === 'admin')
        {!! $section('Langganan') !!}
        {!! $navItem('tagihan.index', 'Tagihan', '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>') !!}
    @endif
@endif
