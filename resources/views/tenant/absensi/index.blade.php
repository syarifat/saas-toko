@extends('layouts.tenant')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <!-- Header Clock Card -->
    <div class="bg-gradient-to-br from-indigo-700 to-indigo-900 text-white p-6 rounded-2xl shadow-md text-center space-y-2">
        <p class="text-xs font-semibold uppercase tracking-widest text-indigo-200" id="live-date">{{ now()->translatedFormat('l, d F Y') }}</p>
        <h2 class="text-4xl font-extrabold tracking-tight font-mono" id="live-clock">{{ now()->format('H:i:s') }}</h2>
        <p class="text-xs text-indigo-200">Karyawan: <span class="font-bold text-white">{{ auth()->user()->nama }}</span> ({{ auth()->user()->karyawan->kode_karyawan ?? 'KRJ' }})</p>
    </div>

    <!-- GPS & Geofencing Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">Status Lokasi GPS Presensi</h3>
            <button type="button" onclick="detectGPS()" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                Perbarui GPS
            </button>
        </div>

        <div id="gps-status-box" class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-2">
            <div class="flex items-center gap-2">
                <span id="gps-indicator" class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
                <span id="gps-message" class="font-medium text-slate-700">Mendeteksi koordinat geolokasi perangkat...</span>
            </div>
            <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-500 pt-2 border-t border-slate-200 font-mono">
                <div>Lat: <span id="display-lat" class="text-slate-800 font-bold">-</span></div>
                <div>Long: <span id="display-long" class="text-slate-800 font-bold">-</span></div>
                <div class="col-span-2">Jarak ke Toko: <span id="display-distance" class="text-slate-800 font-bold">-</span> (Radius Maks: {{ $toko->radius_absensi ?? 100 }}m)</div>
            </div>
        </div>

        <!-- Attendance Action Form -->
        <div class="pt-2">
            @php
                $absensiHariIni = $absensiHariIni ?? null;
            @endphp

            @if(!$absensiHariIni || !$absensiHariIni->jam_masuk)
                <!-- Form Absen Masuk -->
                <form method="POST" action="{{ route('absensi.masuk') }}" enctype="multipart/form-data" id="form-absen-masuk" class="space-y-4">
                    @csrf
                    <input type="hidden" name="lintang" id="input-lat-masuk">
                    <input type="hidden" name="bujur" id="input-long-masuk">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Foto Bukti Selfie / Kamera (Opsional/Wajib)</label>
                        <input type="file" name="foto" accept="image/*" capture="user"
                               class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-xl">
                    </div>

                    <button type="submit" id="btn-absen-masuk" disabled
                            class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl shadow-md transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                        <span>Absen Masuk Sekarang</span>
                    </button>
                </form>
            @elseif(!$absensiHariIni->jam_keluar)
                <!-- Form Absen Keluar -->
                <div class="space-y-4">
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between">
                        <div>
                            <p class="font-bold">Sudah Absen Masuk</p>
                            <p class="text-[11px]">Jam Masuk: {{ $absensiHariIni->jam_masuk->format('H:i:s') }} ({{ ucfirst($absensiHariIni->status ?? 'Tepat Waktu') }})</p>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-emerald-200 text-emerald-900 font-bold text-[10px]">Aktif Bekerja</span>
                    </div>

                    <form method="POST" action="{{ route('absensi.keluar') }}" enctype="multipart/form-data" id="form-absen-keluar" class="space-y-4">
                        @csrf
                        <input type="hidden" name="lintang" id="input-lat-keluar">
                        <input type="hidden" name="bujur" id="input-long-keluar">

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Foto Bukti Keluar / Selfie (Opsional)</label>
                            <input type="file" name="foto" accept="image/*" capture="user"
                                   class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-xl">
                        </div>

                        <button type="submit" id="btn-absen-keluar" disabled
                                class="w-full py-3.5 bg-rose-600 hover:bg-rose-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl shadow-md transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                            <span>Absen Keluar (Pulang)</span>
                        </button>
                    </form>
                </div>
            @else
                <!-- Sudah Selesai Hari Ini -->
                <div class="p-4 rounded-xl bg-slate-100 border border-slate-200 text-center space-y-2 text-xs">
                    <p class="font-bold text-slate-800 text-sm">Presensi Hari Ini Selesai ✓</p>
                    <p class="text-slate-500">
                        Masuk: {{ $absensiHariIni->jam_masuk->format('H:i:s') }} • 
                        Keluar: {{ $absensiHariIni->jam_keluar->format('H:i:s') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
// Live clock
setInterval(() => {
    const now = new Date();
    const clockEl = document.getElementById('live-clock');
    if (clockEl) {
        clockEl.innerText = now.toTimeString().split(' ')[0];
    }
}, 1000);

const storeLat = {{ $toko->garis_lintang ?? 'null' }};
const storeLong = {{ $toko->garis_bujur ?? 'null' }};
const maxRadius = {{ $toko->radius_absensi ?? 100 }};

function calculateDistance(lat1, lon1, lat2, lon2) {
    if (!lat1 || !lon1 || !lat2 || !lon2) return 0;
    const R = 6371000; // meters
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return Math.round(R * c);
}

function detectGPS() {
    const msg = document.getElementById('gps-message');
    const indicator = document.getElementById('gps-indicator');
    const btnMasuk = document.getElementById('btn-absen-masuk');
    const btnKeluar = document.getElementById('btn-absen-keluar');

    if (!navigator.geolocation) {
        msg.innerText = 'Perangkat Anda tidak mendukung geolokasi GPS.';
        indicator.className = 'w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const lat = pos.coords.latitude;
            const long = pos.coords.longitude;

            document.getElementById('display-lat').innerText = lat.toFixed(6);
            document.getElementById('display-long').innerText = long.toFixed(6);

            if (document.getElementById('input-lat-masuk')) {
                document.getElementById('input-lat-masuk').value = lat;
                document.getElementById('input-long-masuk').value = long;
            }
            if (document.getElementById('input-lat-keluar')) {
                document.getElementById('input-lat-keluar').value = lat;
                document.getElementById('input-long-keluar').value = long;
            }

            if (storeLat !== null && storeLong !== null) {
                const dist = calculateDistance(storeLat, storeLong, lat, long);
                document.getElementById('display-distance').innerText = dist + ' meter';

                if (dist <= maxRadius) {
                    msg.innerHTML = `<span class="text-emerald-700 font-bold">Lokasi Valid!</span> Anda berada di dalam radius toko (${dist}m).`;
                    indicator.className = 'w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0';
                    if (btnMasuk) btnMasuk.disabled = false;
                    if (btnKeluar) btnKeluar.disabled = false;
                } else {
                    msg.innerHTML = `<span class="text-rose-700 font-bold">Di Luar Radius!</span> Jarak Anda ${dist}m (Maksimal ${maxRadius}m).`;
                    indicator.className = 'w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0';
                    if (btnMasuk) btnMasuk.disabled = true;
                    if (btnKeluar) btnKeluar.disabled = true;
                }
            } else {
                // Toko belum set koordinat GPS, izinkan absen
                document.getElementById('display-distance').innerText = 'Toko belum set koordinat';
                msg.innerText = 'Lokasi GPS terdeteksi (Toko tanpa geofence).';
                indicator.className = 'w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0';
                if (btnMasuk) btnMasuk.disabled = false;
                if (btnKeluar) btnKeluar.disabled = false;
            }
        },
        (err) => {
            msg.innerText = 'Gagal mendeteksi lokasi GPS: ' + err.message;
            indicator.className = 'w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0';
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

document.addEventListener('DOMContentLoaded', detectGPS);
</script>
@endsection
