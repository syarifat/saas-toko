<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payslip #{{ $penggajian->id }} — {{ $penggajian->karyawan->nama }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; max-width: 700px; margin: 0 auto; padding: 24px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #111; padding-bottom: 12px; margin-bottom: 20px; }
        h1 { font-size: 22px; margin: 0; }
        .muted { color: #666; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        td, th { padding: 7px 10px; text-align: left; font-size: 14px; }
        th { background: #f3f3f3; }
        .r { text-align: right; }
        .total-row { border-top: 2px solid #111; font-weight: bold; font-size: 16px; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; background: #e5f6ec; color: #157347; }
        .badge.draf { background: #fff8dc; color: #8a6d00; }
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>{{ strtoupper($toko->nama) }}</h1>
            <div class="muted">Slip Gaji Karyawan</div>
        </div>
        <div class="muted r">
            No. PGJ-{{ str_pad($penggajian->id, 5, '0', STR_PAD_LEFT) }}<br>
            Periode: {{ $penggajian->periode_mulai->format('d M Y') }} – {{ $penggajian->periode_selesai->format('d M Y') }}
        </div>
    </div>

    <table>
        <tr><th style="width:30%">Karyawan</th><td>{{ $penggajian->karyawan->nama }} ({{ $penggajian->karyawan->kode_karyawan }})</td></tr>
        <tr><th>Posisi</th><td>{{ $penggajian->karyawan->posisi ?? '-' }}</td></tr>
        <tr><th>Skema Gaji</th><td>{{ ucfirst($penggajian->skema_gaji_snapshot) }}</td></tr>
        <tr><th>Status</th><td><span class="badge {{ $penggajian->status }}">{{ ucfirst($penggajian->status) }}</span></td></tr>
    </table>

    <h3 style="margin-top:24px;">Rincian</h3>
    <table>
        <tr>
            <th>Keterangan</th>
            <th class="r">Jumlah</th>
        </tr>
        <tr>
            <td>Gaji Dasar {{ $penggajian->skema_gaji_snapshot === 'harian' ? '( '.$penggajian->jumlah_hadir.' hari hadir )' : '( pokok bulanan )' }}</td>
            <td class="r">Rp {{ number_format($penggajian->jumlah_dasar, 0, ',', '.') }}</td>
        </tr>
        @foreach ($penggajian->komponen()->where('jenis', 'tunjangan')->get() as $k)
            <tr><td>Tunjangan: {{ $k->nama }}</td><td class="r">Rp {{ number_format($k->nominal, 0, ',', '.') }}</td></tr>
        @endforeach
        @foreach ($penggajian->komponen()->where('jenis', 'potongan')->get() as $k)
            <tr><td>Potongan: {{ $k->nama }}</td><td class="r">(Rp {{ number_format($k->nominal, 0, ',', '.') }})</td></tr>
        @endforeach
        <tr class="total-row">
            <td>Gaji Bersih</td>
            <td class="r">Rp {{ number_format($penggajian->gaji_bersih, 0, ',', '.') }}</td>
        </tr>
    </table>

    <p class="muted" style="margin-top:32px;">
        @if ($penggajian->dibayar_pada)
            Dibayarkan pada {{ $penggajian->dibayar_pada->translatedFormat('d F Y H:i') }}.
        @else
            Slip ini masih berstatus draf dan belum dibayarkan.
        @endif
    </p>

    <button onclick="window.print()" style="padding:10px 20px; cursor:pointer;">🖨 Cetak / Simpan PDF</button>
</body>
</html>
