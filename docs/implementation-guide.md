# Panduan Implementasi — Mega System SaaS Toko (Modular)
**Untuk: AI Agent Eksekutor**

> Baca dokumen ini lengkap sebelum menulis satu baris kode pun.
> Kerjakan **satu fase selesai** sebelum lanjut ke fase berikutnya.
> Setiap fase diakhiri dengan perintah verifikasi yang **harus lulus** sebelum lanjut.

---

## Konteks Proyek

| Item | Nilai |
|------|-------|
| Framework | Laravel 13, PHP 8.4 |
| Frontend | Blade + Tailwind CSS v4 + Vite |
| Database | MySQL (single-DB multi-tenant via `toko_id`) |
| Auth | Laravel Breeze (sudah terpasang) |
| Testing | PHPUnit (feature tests) |
| Naming | **Semua tabel dan field berbahasa Indonesia** (snake_case) |
| Status awal | Fresh Breeze install — factories sudah ada di `database/factories/`, models belum ada |

## Dokumen Referensi (Wajib Dibaca Sebelum Mulai)

```bash
cat docs/erd.md                    # skema lengkap semua tabel
cat docs/kebutuhan-fungsional.md   # fitur per role dan per modul
cat docs/README-proyek.md          # arsitektur dan konvensi
cat AGENTS.md                      # konvensi koding wajib
```

## Aturan Umum (Wajib Dipatuhi)

1. Jalankan `vendor/bin/pint --dirty` setiap selesai edit file PHP
2. Jalankan `php artisan test --compact` setiap selesai satu fase
3. Gunakan `php artisan make:` untuk buat file baru
4. Cek sibling files sebelum buat file baru (ikuti pola yang ada)
5. **Setiap model tenant** harus `use BelongsToToko` agar auto-isolasi by `toko_id`
6. Gate modul di route: `middleware('modul:kode_modul')` — bukan `CekPaket` atau `CekAddon`
7. Baca `docs/erd.md` sebelum membuat migration apapun

---

## FASE 0: Orientasi & Verifikasi Lingkungan

### Tujuan
Pastikan environment berjalan dan pahami kondisi awal codebase.

### Langkah

```bash
# 1. Cek versi
php artisan --version   # Laravel 13.x
php -v                  # PHP 8.4.x

# 2. Jalankan migration awal Breeze
php artisan migrate

# 3. Jalankan test awal (harus lulus semua — 8 test Breeze bawaan)
php artisan test --compact

# 4. Baca factories yang sudah ada sebagai referensi field names
# database/factories/TokoFactory.php
# database/factories/PaketFactory.php
# database/factories/ProdukFactory.php
# database/factories/KaryawanFactory.php
# database/factories/TransaksiFactory.php
# database/factories/PenjualanSederhanaFactory.php
# database/factories/PengeluaranFactory.php
# database/factories/UserFactory.php

# 5. Pastikan npm build berjalan
npm run build
```

### Kondisi Awal
- `app/Models/User.php` — akan direname ke `Pengguna.php`
- `app/Http/Controllers/Auth/` — Breeze auth, **jangan diubah**
- `database/migrations/0001_01_01_000000_create_users_table.php` — akan dimodifikasi
- `database/factories/` — 8 factories, model-nya belum ada

### ✅ Selesai Jika
`php artisan test --compact` lulus semua (8 test Breeze).

---

## FASE 1: Database — Migration Semua Tabel

### Tujuan
Buat semua migration domain. **Urutan penting** karena ada foreign key dependencies.

### 1.1 Modifikasi tabel `users` → `pengguna`

```bash
php artisan make:migration modifikasi_tabel_pengguna --table=users
```

**`up()` isi:**
```php
Schema::table('users', function (Blueprint $table) {
    $table->renameColumn('name', 'nama');
    $table->unsignedBigInteger('toko_id')->nullable()->after('id');
    $table->enum('peran', ['superadmin', 'admin', 'karyawan'])->default('karyawan')->after('email');
    $table->enum('sub_peran', ['kasir', 'gudang'])->nullable()->after('peran');
    $table->boolean('aktif')->default(true)->after('sub_peran');
    $table->unsignedBigInteger('dibuat_oleh')->nullable()->after('aktif');
});
Schema::rename('users', 'pengguna');
```

**`down()` isi:**
```php
Schema::rename('pengguna', 'users');
Schema::table('users', function (Blueprint $table) {
    $table->renameColumn('nama', 'name');
    $table->dropColumn(['toko_id', 'peran', 'sub_peran', 'aktif', 'dibuat_oleh']);
});
```

### 1.2 Tabel `paket`

```bash
php artisan make:migration create_paket_table
```

Schema:
```php
$table->id();
$table->string('nama');
$table->enum('jenis', ['preset_1', 'preset_2', 'preset_3', 'custom']);
$table->decimal('harga', 12, 2)->default(0);
$table->text('deskripsi')->nullable();
$table->boolean('aktif')->default(true);
$table->timestamps();
```

### 1.3 Tabel `toko`

```bash
php artisan make:migration create_toko_table
```

Schema:
```php
$table->id();
$table->string('nama');
$table->string('slug')->unique();
$table->unsignedBigInteger('paket_id');
$table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
$table->decimal('garis_lintang', 10, 7)->nullable();
$table->decimal('garis_bujur', 10, 7)->nullable();
$table->integer('radius_absensi')->default(100);  // meter
$table->timestamp('langganan_berakhir_pada')->nullable();
$table->timestamps();

$table->foreign('paket_id')->references('id')->on('paket');
```

Setelah tabel toko ada, tambah FK dari pengguna ke toko:

```bash
php artisan make:migration tambah_fk_pengguna_toko --table=pengguna
```

```php
// up()
$table->foreign('toko_id')->references('id')->on('toko')->nullOnDelete();
$table->foreign('dibuat_oleh')->references('id')->on('pengguna')->nullOnDelete();
```

### 1.4 Tabel `modul`

```bash
php artisan make:migration create_modul_table
```

Schema:
```php
$table->id();
$table->string('kode')->unique();   // 'master_produk', 'kasir_pos', dll
$table->string('nama');
$table->text('deskripsi')->nullable();
$table->boolean('aktif')->default(true);
$table->timestamps();
```

### 1.5 Tabel `ketergantungan_modul`

```bash
php artisan make:migration create_ketergantungan_modul_table
```

Schema:
```php
$table->unsignedBigInteger('modul_id');
$table->unsignedBigInteger('requires_modul_id');
$table->primary(['modul_id', 'requires_modul_id']);
$table->foreign('modul_id')->references('id')->on('modul')->cascadeOnDelete();
$table->foreign('requires_modul_id')->references('id')->on('modul')->cascadeOnDelete();
```

### 1.6 Tabel `paket_modul`

```bash
php artisan make:migration create_paket_modul_table
```

Schema:
```php
$table->unsignedBigInteger('paket_id');
$table->unsignedBigInteger('modul_id');
$table->primary(['paket_id', 'modul_id']);
$table->foreign('paket_id')->references('id')->on('paket')->cascadeOnDelete();
$table->foreign('modul_id')->references('id')->on('modul')->cascadeOnDelete();
```

### 1.7 Tabel `modul_toko`

```bash
php artisan make:migration create_modul_toko_table
```

Schema:
```php
$table->unsignedBigInteger('toko_id');
$table->unsignedBigInteger('modul_id');
$table->primary(['toko_id', 'modul_id']);
$table->boolean('aktif')->default(true);
$table->timestamp('diaktifkan_pada')->nullable();
$table->timestamp('berakhir_pada')->nullable();
$table->foreign('toko_id')->references('id')->on('toko')->cascadeOnDelete();
$table->foreign('modul_id')->references('id')->on('modul')->cascadeOnDelete();
```

### 1.8 Tabel `kategori`

Schema: `id, toko_id (FK→toko cascadeOnDelete), nama, timestamps`

### 1.9 Tabel `pemasok`

Schema: `id, toko_id, nama, telepon (nullable), alamat (text nullable), timestamps`

### 1.10 Tabel `produk`

```php
$table->id();
$table->unsignedBigInteger('toko_id');
$table->unsignedBigInteger('kategori_id')->nullable();
$table->unsignedBigInteger('pemasok_id')->nullable();
$table->string('sku');
$table->string('nama');
$table->decimal('harga_beli', 12, 2)->default(0);
$table->decimal('harga_jual', 12, 2)->default(0);
$table->integer('stok_minimum')->default(0);
$table->timestamps();

$table->unique(['toko_id', 'sku']);
// FKs: toko_id, kategori_id (nullOnDelete), pemasok_id (nullOnDelete)
```

### 1.11 Tabel `gudang`

```php
$table->id();
$table->unsignedBigInteger('toko_id');
$table->string('nama');
$table->enum('jenis', ['etalase', 'gudang'])->default('etalase');
$table->timestamps();
// FK: toko_id cascadeOnDelete
```

### 1.12 Tabel `stok_gudang`

```php
$table->id();
$table->unsignedBigInteger('toko_id');
$table->unsignedBigInteger('produk_id');
$table->unsignedBigInteger('gudang_id');
$table->integer('jumlah')->default(0);
$table->timestamps();

$table->unique(['produk_id', 'gudang_id']);
// FKs: toko_id, produk_id, gudang_id
```

### 1.13 Tabel `pergerakan_stok`

```php
$table->id();
$table->unsignedBigInteger('toko_id');
$table->unsignedBigInteger('produk_id');
$table->unsignedBigInteger('gudang_id');
$table->unsignedBigInteger('gudang_tujuan_id')->nullable();
$table->enum('jenis', ['masuk', 'keluar', 'transfer', 'penjualan', 'opname']);
$table->integer('jumlah');  // positif = masuk, negatif = keluar
$table->nullableMorphs('referensi');  // referensi_tipe + referensi_id
$table->text('catatan')->nullable();
$table->timestamps();
```

### 1.14 Tabel `pengeluaran`

```php
$table->id();
$table->unsignedBigInteger('toko_id');
$table->unsignedBigInteger('pengguna_id');
$table->date('tanggal_pengeluaran');
$table->string('keterangan');
$table->decimal('nominal', 12, 2);
$table->string('bukti_struk')->nullable();  // path file upload
$table->timestamps();
```

### 1.15 Tabel `penjualan_sederhana`

Schema: `id, toko_id, pengguna_id (FK), tanggal_penjualan (date), total (decimal 12,2), catatan (text nullable), timestamps`

### 1.16 Tabel `item_penjualan_sederhana`

```php
$table->id();
$table->unsignedBigInteger('toko_id');
$table->unsignedBigInteger('penjualan_sederhana_id');
$table->unsignedBigInteger('produk_id');
$table->string('nama_produk');          // snapshot nama saat transaksi
$table->integer('jumlah');
$table->decimal('harga_satuan', 12, 2);
$table->decimal('subtotal', 12, 2);
$table->decimal('harga_beli_snapshot', 12, 2)->default(0);
$table->timestamps();
// FK: penjualan_sederhana_id cascadeOnDelete, produk_id restrictOnDelete
```

### 1.17 Tabel `transaksi`

```php
$table->id();
$table->unsignedBigInteger('toko_id');
$table->unsignedBigInteger('pengguna_id');  // kasir
$table->unsignedBigInteger('gudang_id');
$table->date('tanggal_transaksi');
$table->decimal('subtotal', 12, 2)->default(0);
$table->decimal('diskon', 12, 2)->default(0);
$table->decimal('total', 12, 2)->default(0);
$table->decimal('jumlah_bayar', 12, 2)->default(0);
$table->decimal('kembalian', 12, 2)->default(0);
$table->enum('metode_pembayaran', ['tunai', 'qris', 'transfer'])->default('tunai');
$table->timestamps();
```

### 1.18 Tabel `item_transaksi`

```php
$table->id();
$table->unsignedBigInteger('toko_id');
$table->unsignedBigInteger('transaksi_id');
$table->unsignedBigInteger('produk_id');
$table->string('nama_produk');           // snapshot
$table->integer('jumlah');
$table->decimal('harga_satuan', 12, 2);
$table->decimal('subtotal', 12, 2);
$table->decimal('harga_beli_snapshot', 12, 2)->default(0);
$table->timestamps();
// FK: transaksi_id cascadeOnDelete
```

### 1.19 Tabel `karyawan`

```php
$table->id();
$table->unsignedBigInteger('toko_id');
$table->unsignedBigInteger('pengguna_id');  // NOT nullable — wajib punya akun login
$table->string('kode_karyawan');
$table->string('posisi')->nullable();
$table->enum('skema_gaji', ['harian', 'bulanan'])->default('bulanan');
$table->decimal('tarif_harian', 12, 2)->default(0);
$table->decimal('gaji_pokok', 12, 2)->default(0);
$table->date('tanggal_masuk');
$table->boolean('aktif')->default(true);
$table->timestamps();

$table->unique(['toko_id', 'kode_karyawan']);
$table->foreign('pengguna_id')->references('id')->on('pengguna');
```

### 1.20 Tabel `absensi`

```php
$table->id();
$table->unsignedBigInteger('toko_id');
$table->unsignedBigInteger('karyawan_id');
$table->date('tanggal');
$table->timestamp('jam_masuk')->nullable();
$table->timestamp('jam_keluar')->nullable();
$table->decimal('lintang_masuk', 10, 7)->nullable();
$table->decimal('bujur_masuk', 10, 7)->nullable();
$table->decimal('lintang_keluar', 10, 7)->nullable();
$table->decimal('bujur_keluar', 10, 7)->nullable();
$table->string('foto_masuk')->nullable();
$table->string('foto_keluar')->nullable();
$table->enum('status', ['tepat_waktu', 'telat'])->nullable();
$table->integer('menit_telat')->default(0);
$table->integer('menit_lembur')->default(0);
$table->timestamps();

$table->unique(['toko_id', 'karyawan_id', 'tanggal']);
```

### 1.21 Tabel `penggajian`

```php
$table->id();
$table->unsignedBigInteger('toko_id');
$table->unsignedBigInteger('karyawan_id');
$table->date('periode_mulai');
$table->date('periode_selesai');
$table->enum('skema_gaji_snapshot', ['harian', 'bulanan']);
$table->decimal('jumlah_dasar', 12, 2)->default(0);
$table->decimal('total_tunjangan', 12, 2)->default(0);
$table->decimal('total_potongan', 12, 2)->default(0);
$table->decimal('gaji_bersih', 12, 2)->default(0);
$table->enum('status', ['draf', 'dibayar'])->default('draf');
$table->timestamp('dibayar_pada')->nullable();
$table->timestamps();
```

### 1.22 Tabel `komponen_gaji`

Schema: `id, toko_id, penggajian_id (FK cascadeOnDelete), jenis (enum: tunjangan/potongan), nama (string), nominal (decimal 12,2), timestamps`

### 1.23 Tabel `pembayaran`

```php
$table->id();
$table->unsignedBigInteger('toko_id');
$table->enum('jenis', ['upgrade_paket', 'aktivasi_addon']);
$table->unsignedBigInteger('paket_id')->nullable();
$table->unsignedBigInteger('modul_id')->nullable();  // untuk aktivasi modul individual
$table->decimal('jumlah', 12, 2);
$table->string('bukti_transfer');   // path file
$table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
$table->text('catatan_penolakan')->nullable();
$table->unsignedBigInteger('diverifikasi_oleh')->nullable();  // FK → pengguna
$table->timestamp('diverifikasi_pada')->nullable();
$table->timestamps();
// FKs: toko_id, paket_id (nullOnDelete), modul_id (nullOnDelete), diverifikasi_oleh (nullOnDelete)
```

### Jalankan

```bash
php artisan migrate
```

### ✅ Selesai Jika

```bash
php artisan migrate:status
# Semua baris: Ran ✓

php artisan tinker --execute 'Schema::getColumnListing("modul_toko");'
# Output: ['toko_id', 'modul_id', 'aktif', 'diaktifkan_pada', 'berakhir_pada']
```

---

## FASE 2: Model Layer

### Tujuan
Buat semua Eloquent model dengan trait, relasi, fillable, dan casts.

### 2.1 Rename `User` → `Pengguna`

**Langkah-langkah:**

```bash
# 1. Rename file
mv app/Models/User.php app/Models/Pengguna.php

# 2. Edit app/Models/Pengguna.php
# 3. Update config/auth.php
# 4. Update database/factories/UserFactory.php
# 5. Update semua Breeze controllers yang import User
```

**Isi `app/Models/Pengguna.php`:**

```php
<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'toko_id', 'nama', 'email', 'password',
        'peran', 'sub_peran', 'aktif', 'dibuat_oleh',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'aktif' => 'boolean',
        ];
    }

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }

    public function pembuatAkun(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'dibuat_oleh');
    }

    public function karyawan(): HasOne
    {
        return $this->hasOne(Karyawan::class, 'pengguna_id');
    }

    public function isSuperadmin(): bool
    {
        return $this->peran === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return $this->peran === 'admin';
    }

    public function isKaryawan(): bool
    {
        return $this->peran === 'karyawan';
    }

    public function isKasir(): bool
    {
        return $this->sub_peran === 'kasir';
    }

    public function isGudang(): bool
    {
        return $this->sub_peran === 'gudang';
    }
}
```

**Update `config/auth.php`:**
```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\Pengguna::class,
    ],
],
```

**Update semua Breeze auth controllers:** Ganti `use App\Models\User;` → `use App\Models\Pengguna;` dan `User::` → `Pengguna::`.

### 2.2 Trait `BelongsToToko`

**File:** `app/Models/Concerns/BelongsToToko.php`

```php
<?php

namespace App\Models\Concerns;

use App\Models\Toko;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToToko
{
    public static function bootBelongsToToko(): void
    {
        static::addGlobalScope('toko', function ($query) {
            if (auth()->check() && auth()->user()->toko_id) {
                $query->where(
                    (new static)->qualifyColumn('toko_id'),
                    auth()->user()->toko_id
                );
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->toko_id && empty($model->toko_id)) {
                $model->toko_id = auth()->user()->toko_id;
            }
        });
    }

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }
}
```

### 2.3 Model `Toko`

**File:** `app/Models/Toko.php`

```php
<?php

namespace App\Models;

use App\Services\ModulService;
use Database\Factories\TokoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Toko extends Model
{
    /** @use HasFactory<TokoFactory> */
    use HasFactory;

    protected $table = 'toko';

    protected $fillable = [
        'nama', 'slug', 'paket_id', 'status',
        'garis_lintang', 'garis_bujur', 'radius_absensi',
        'langganan_berakhir_pada',
    ];

    protected function casts(): array
    {
        return [
            'garis_lintang' => 'decimal:7',
            'garis_bujur' => 'decimal:7',
            'langganan_berakhir_pada' => 'datetime',
        ];
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class);
    }

    public function pengguna(): HasMany
    {
        return $this->hasMany(Pengguna::class);
    }

    public function modulToko(): HasMany
    {
        return $this->hasMany(ModulToko::class);
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    /**
     * Cek apakah modul tertentu aktif untuk toko ini.
     */
    public function modulAktif(string $kode): bool
    {
        return $this->modulToko()
            ->whereHas('modul', fn ($q) => $q->where('kode', $kode))
            ->where('aktif', true)
            ->exists();
    }

    /**
     * Aktifkan semua modul dari preset paket.
     */
    public function pakaiPreset(Paket $paket): void
    {
        app(ModulService::class)->pakaiPreset($this, $paket);
    }
}
```

### 2.4 Model `Paket`

```php
<?php

namespace App\Models;

use Database\Factories\PaketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paket extends Model
{
    /** @use HasFactory<PaketFactory> */
    use HasFactory;

    protected $table = 'paket';

    protected $fillable = ['nama', 'jenis', 'harga', 'deskripsi', 'aktif'];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'harga' => 'decimal:2',
        ];
    }

    public function toko(): HasMany
    {
        return $this->hasMany(Toko::class);
    }

    public function modul(): BelongsToMany
    {
        return $this->belongsToMany(Modul::class, 'paket_modul');
    }
}
```

### 2.5 Model `Modul`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Modul extends Model
{
    protected $table = 'modul';

    protected $fillable = ['kode', 'nama', 'deskripsi', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function paket(): BelongsToMany
    {
        return $this->belongsToMany(Paket::class, 'paket_modul');
    }

    public function toko(): BelongsToMany
    {
        return $this->belongsToMany(Toko::class, 'modul_toko')
            ->withPivot(['aktif', 'diaktifkan_pada', 'berakhir_pada']);
    }

    /**
     * Modul yang wajib aktif SEBELUM modul ini bisa diaktifkan.
     */
    public function ketergantungan(): BelongsToMany
    {
        return $this->belongsToMany(
            Modul::class,
            'ketergantungan_modul',
            'modul_id',
            'requires_modul_id'
        );
    }

    /**
     * Modul yang BERGANTUNG pada modul ini.
     */
    public function dependan(): BelongsToMany
    {
        return $this->belongsToMany(
            Modul::class,
            'ketergantungan_modul',
            'requires_modul_id',
            'modul_id'
        );
    }
}
```

### 2.6 Model `ModulToko`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModulToko extends Model
{
    protected $table = 'modul_toko';

    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = ['toko_id', 'modul_id', 'aktif', 'diaktifkan_pada', 'berakhir_pada'];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'diaktifkan_pada' => 'datetime',
            'berakhir_pada' => 'datetime',
        ];
    }

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }

    public function modul(): BelongsTo
    {
        return $this->belongsTo(Modul::class);
    }
}
```

### 2.7 Model-model Domain (Semua Pakai `BelongsToToko`)

Buat satu per satu dengan artisan:

```bash
php artisan make:model Kategori
php artisan make:model Pemasok
php artisan make:model Produk
php artisan make:model Gudang
php artisan make:model StokGudang
php artisan make:model PergerakanStok
php artisan make:model Pengeluaran
php artisan make:model PenjualanSederhana
php artisan make:model ItemPenjualanSederhana
php artisan make:model Transaksi
php artisan make:model ItemTransaksi
php artisan make:model Karyawan
php artisan make:model Absensi
php artisan make:model Penggajian
php artisan make:model KomponenGaji
php artisan make:model Pembayaran
```

**Template pola setiap model tenant:**

```php
<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NamaModel extends Model
{
    use HasFactory, BelongsToToko;

    protected $table = 'nama_tabel';

    protected $fillable = [...];

    // relasi-relasi
}
```

**Relasi penting per model:**

| Model | `$table` | Relasi utama |
|-------|----------|-------------|
| `Kategori` | `kategori` | `produk(): HasMany` |
| `Pemasok` | `pemasok` | `produk(): HasMany` |
| `Produk` | `produk` | `kategori()`, `pemasok()`, `stokGudang(): HasMany` |
| `Gudang` | `gudang` | `stokGudang(): HasMany`, `pergerakanStok(): HasMany` |
| `StokGudang` | `stok_gudang` | `produk()`, `gudang()` |
| `PergerakanStok` | `pergerakan_stok` | `produk()`, `gudang()`, `gudangTujuan()`, `referensi(): MorphTo` |
| `Pengeluaran` | `pengeluaran` | `pengguna(): BelongsTo` |
| `PenjualanSederhana` | `penjualan_sederhana` | `items(): HasMany → ItemPenjualanSederhana`, `pengguna()` |
| `ItemPenjualanSederhana` | `item_penjualan_sederhana` | `penjualanSederhana()`, `produk()` |
| `Transaksi` | `transaksi` | `items(): HasMany → ItemTransaksi`, `pengguna()`, `gudang()` |
| `ItemTransaksi` | `item_transaksi` | `transaksi()`, `produk()` |
| `Karyawan` | `karyawan` | `pengguna()`, `absensi(): HasMany`, `penggajian(): HasMany` |
| `Absensi` | `absensi` | `karyawan()` |
| `Penggajian` | `penggajian` | `karyawan()`, `komponen(): HasMany → KomponenGaji` |
| `KomponenGaji` | `komponen_gaji` | `penggajian()` |
| `Pembayaran` | `pembayaran` | `toko()`, `paket()`, `modul()`, `diverifikasiOleh(): BelongsTo(Pengguna)` |

**Catatan penting untuk `StokGudang`:**
```php
protected $table = 'stok_gudang';
// Tidak ada auto-increment PK yang standar, gunakan id bigint
```

**`PergerakanStok` morphable:**
```php
public function referensi(): MorphTo
{
    return $this->morphTo('referensi');
}
```

### ✅ Selesai Jika

```bash
php artisan tinker --execute '
    App\Models\Modul::count();
    App\Models\Toko::count();
    App\Models\Pengguna::count();
'
# Semua output 0 (tabel ada, kosong)

php artisan test --compact
# Semua test lulus
```

---

## FASE 3: Service Layer

### 3.1 `ModulService`

**File:** `app/Services/ModulService.php`

```php
<?php

namespace App\Services;

use App\Models\Modul;
use App\Models\ModulToko;
use App\Models\Paket;
use App\Models\Toko;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ModulService
{
    /**
     * Aktifkan satu modul untuk toko.
     * Lempar ValidationException jika ada dependency yang belum aktif.
     */
    public function aktifkan(Toko $toko, string $kodeModul): void
    {
        $modul = Modul::where('kode', $kodeModul)->firstOrFail();
        $belumAktif = $this->getDependencyBelumAktif($toko, $modul);

        if ($belumAktif->isNotEmpty()) {
            $nama = $belumAktif->pluck('nama')->join(', ');
            throw ValidationException::withMessages([
                'modul' => "Modul [{$modul->nama}] membutuhkan modul berikut aktif lebih dulu: {$nama}.",
            ]);
        }

        ModulToko::updateOrCreate(
            ['toko_id' => $toko->id, 'modul_id' => $modul->id],
            ['aktif' => true, 'diaktifkan_pada' => now()]
        );
    }

    /**
     * Aktifkan modul beserta semua dependency-nya (rekursif).
     */
    public function aktifkanDenganDependency(Toko $toko, string $kodeModul): void
    {
        $modul = Modul::where('kode', $kodeModul)->firstOrFail();
        $deps = $this->semuaDependencyRekursif($modul);

        foreach ($deps as $dep) {
            if (! $toko->modulAktif($dep->kode)) {
                ModulToko::updateOrCreate(
                    ['toko_id' => $toko->id, 'modul_id' => $dep->id],
                    ['aktif' => true, 'diaktifkan_pada' => now()]
                );
            }
        }

        ModulToko::updateOrCreate(
            ['toko_id' => $toko->id, 'modul_id' => $modul->id],
            ['aktif' => true, 'diaktifkan_pada' => now()]
        );
    }

    /**
     * Nonaktifkan modul.
     * Lempar ValidationException jika ada modul dependen yang masih aktif.
     */
    public function nonaktifkan(Toko $toko, string $kodeModul): void
    {
        $modul = Modul::where('kode', $kodeModul)->firstOrFail();
        $dependanAktif = $this->getDependanAktif($toko, $modul);

        if ($dependanAktif->isNotEmpty()) {
            $nama = $dependanAktif->pluck('nama')->join(', ');
            throw ValidationException::withMessages([
                'modul' => "Tidak bisa menonaktifkan [{$modul->nama}]. Modul berikut bergantung padanya: {$nama}.",
            ]);
        }

        ModulToko::where('toko_id', $toko->id)
            ->where('modul_id', $modul->id)
            ->update(['aktif' => false]);
    }

    /**
     * Sinkronisasi semua modul dari preset ke toko.
     */
    public function pakaiPreset(Toko $toko, Paket $paket): void
    {
        $paket->loadMissing('modul');

        foreach ($paket->modul as $modul) {
            ModulToko::updateOrCreate(
                ['toko_id' => $toko->id, 'modul_id' => $modul->id],
                ['aktif' => true, 'diaktifkan_pada' => now()]
            );
        }

        $toko->update(['paket_id' => $paket->id]);
    }

    /**
     * Ambil dependency yang belum aktif untuk toko.
     */
    public function getDependencyBelumAktif(Toko $toko, Modul $modul): Collection
    {
        $modul->loadMissing('ketergantungan');

        return $modul->ketergantungan->filter(
            fn ($dep) => ! $toko->modulAktif($dep->kode)
        );
    }

    /**
     * Ambil modul dependen yang masih aktif untuk toko.
     */
    public function getDependanAktif(Toko $toko, Modul $modul): Collection
    {
        $modul->loadMissing('dependan');

        return $modul->dependan->filter(
            fn ($dep) => $toko->modulAktif($dep->kode)
        );
    }

    /**
     * Rekursif DFS: ambil semua dependency terurut (daun dulu).
     */
    private function semuaDependencyRekursif(Modul $modul, array &$visited = []): Collection
    {
        $modul->loadMissing('ketergantungan');
        $result = collect();

        foreach ($modul->ketergantungan as $dep) {
            if (! in_array($dep->id, $visited)) {
                $visited[] = $dep->id;
                $result = $result->merge($this->semuaDependencyRekursif($dep, $visited));
                $result->push($dep);
            }
        }

        return $result;
    }
}
```

### 3.2 `StokService`

**File:** `app/Services/StokService.php`

Method yang **wajib** diimplementasikan:

```php
public function kurangiStok(Produk $produk, Gudang $gudang, int $jumlah, string $referensiTipe, int $referensiId, string $catatan = ''): void
// Validasi stok cukup (throw Exception jika tidak)
// Pakai DB::transaction + lockForUpdate
// Update stok_gudang + tulis pergerakan_stok jenis='penjualan'

public function tambahStok(Produk $produk, Gudang $gudang, int $jumlah, string $referensiTipe = '', int $referensiId = 0, string $catatan = ''): void
// updateOrCreate stok_gudang + tulis pergerakan_stok jenis='masuk'

public function transferStok(Produk $produk, Gudang $gudangAsal, Gudang $gudangTujuan, int $jumlah, string $catatan = ''): void
// Kurangi stok gudang asal, tambah gudang tujuan, tulis 2 pergerakan_stok jenis='transfer'

public function opname(Produk $produk, Gudang $gudang, int $jumlahFisik, string $catatan = ''): void
// Hitung selisih (jumlahFisik - stok_saat_ini), update stok, tulis pergerakan_stok jenis='opname'
```

### ✅ Selesai Jika

```bash
php artisan test --compact
# Semua test lulus
```

---

## FASE 4: Middleware

### 4.1 `app/Http/Middleware/Peran.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Peran
{
    /**
     * Penggunaan di route: ->middleware('peran:superadmin')
     *                      ->middleware('peran:admin,karyawan')
     */
    public function handle(Request $request, Closure $next, string ...$peran): mixed
    {
        if (! $request->user() || ! in_array($request->user()->peran, $peran)) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
```

### 4.2 `app/Http/Middleware/EnsureTokoContext.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTokoContext
{
    public function handle(Request $request, Closure $next): mixed
    {
        $pengguna = $request->user();

        if ($pengguna && ! $pengguna->isSuperadmin() && ! $pengguna->toko_id) {
            abort(403, 'Konteks toko tidak ditemukan.');
        }

        return $next($request);
    }
}
```

### 4.3 `app/Http/Middleware/CekModul.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CekModul
{
    /**
     * Penggunaan di route: ->middleware('modul:kasir_pos')
     */
    public function handle(Request $request, Closure $next, string $kodeModul): mixed
    {
        $pengguna = $request->user();

        if (! $pengguna?->toko) {
            abort(403);
        }

        if (! $pengguna->toko->modulAktif($kodeModul)) {
            return redirect()->route('dashboard')
                ->with('error', 'Fitur ini tidak tersedia di paket Anda saat ini.');
        }

        return $next($request);
    }
}
```

### 4.4 Daftarkan di `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'peran'        => \App\Http\Middleware\Peran::class,
        'konteks_toko' => \App\Http\Middleware\EnsureTokoContext::class,
        'modul'        => \App\Http\Middleware\CekModul::class,
    ]);
})
```

### ✅ Selesai Jika

```bash
php artisan route:list --compact
# Middleware alias terdaftar tanpa error

php artisan test --compact
```

---

## FASE 5: Seeder

### 5.1 `database/seeders/ModulSeeder.php`

```bash
php artisan make:seeder ModulSeeder
```

Seed 16 modul dalam urutan ini:

```php
$modul = [
    ['kode' => 'pengeluaran',       'nama' => 'Pencatatan Pengeluaran',          'deskripsi' => 'Catat pengeluaran operasional toko.'],
    ['kode' => 'master_produk',     'nama' => 'Master Produk/Kategori/Pemasok',  'deskripsi' => 'Kelola data produk, kategori, dan pemasok.'],
    ['kode' => 'penjualan_ringkas', 'nama' => 'Penjualan Ringkas',               'deskripsi' => 'Catat penjualan dengan pilih produk dari master.'],
    ['kode' => 'rekap_keuangan',    'nama' => 'Rekap & Laba Kotor',              'deskripsi' => 'Lihat rekap uang masuk/keluar dan estimasi laba.'],
    ['kode' => 'stok_gudang',       'nama' => 'Manajemen Stok',                  'deskripsi' => 'Kelola stok produk per gudang.'],
    ['kode' => 'kasir_pos',         'nama' => 'Kasir POS',                       'deskripsi' => 'Transaksi kasir dengan auto deduct stok.'],
    ['kode' => 'stock_alert',       'nama' => 'Alert Stok Menipis',              'deskripsi' => 'Notifikasi produk stok di bawah minimum.'],
    ['kode' => 'stok_opname',       'nama' => 'Stok Opname/Adjustment',          'deskripsi' => 'Penyesuaian stok fisik vs sistem.'],
    ['kode' => 'laporan_hpp',       'nama' => 'Laporan Laba per Produk (HPP)',   'deskripsi' => 'Laporan laba kotor per produk berdasarkan HPP.'],
    ['kode' => 'multi_gudang',      'nama' => 'Multi Gudang',                    'deskripsi' => 'Kelola lebih dari satu gudang/etalase.'],
    ['kode' => 'barang_masuk',      'nama' => 'Barang Masuk dari Supplier',      'deskripsi' => 'Catat penerimaan barang dari pemasok.'],
    ['kode' => 'transfer_gudang',   'nama' => 'Transfer Antar Gudang',           'deskripsi' => 'Pindahkan stok antar gudang.'],
    ['kode' => 'kartu_stok',        'nama' => 'Kartu Stok Detail',               'deskripsi' => 'Histori pergerakan stok per produk.'],
    ['kode' => 'karyawan',          'nama' => 'HRIS Karyawan',                   'deskripsi' => 'Kelola data karyawan dan skema gaji.'],
    ['kode' => 'absensi',           'nama' => 'Absensi GPS',                     'deskripsi' => 'Presensi karyawan berbasis geolokasi.'],
    ['kode' => 'payroll',           'nama' => 'Penggajian/Payroll',              'deskripsi' => 'Hitung dan bayar gaji karyawan.'],
];

foreach ($modul as $data) {
    Modul::create($data);
}
```

### 5.2 `database/seeders/KetergantunganModulSeeder.php`

```bash
php artisan make:seeder KetergantunganModulSeeder
```

```php
$deps = [
    'penjualan_ringkas' => ['master_produk'],
    'rekap_keuangan'    => ['penjualan_ringkas'],
    'stok_gudang'       => ['master_produk'],
    'kasir_pos'         => ['master_produk', 'stok_gudang'],
    'stock_alert'       => ['stok_gudang'],
    'stok_opname'       => ['stok_gudang'],
    'barang_masuk'      => ['stok_gudang'],
    'kartu_stok'        => ['stok_gudang'],
    'laporan_hpp'       => ['kasir_pos'],
    'multi_gudang'      => ['stok_gudang'],
    'transfer_gudang'   => ['multi_gudang'],
    'absensi'           => ['karyawan'],
    'payroll'           => ['absensi'],
];

foreach ($deps as $kodeModul => $kodeRequires) {
    $modul = Modul::where('kode', $kodeModul)->first();
    foreach ($kodeRequires as $kodeReq) {
        $req = Modul::where('kode', $kodeReq)->first();
        DB::table('ketergantungan_modul')->insert([
            'modul_id' => $modul->id,
            'requires_modul_id' => $req->id,
        ]);
    }
}
```

### 5.3 `database/seeders/PaketSeeder.php`

```bash
php artisan make:seeder PaketSeeder
```

```php
// Preset 1 — 4 modul
$preset1 = Paket::create([
    'nama' => 'Paket 1 — Cashbook',
    'jenis' => 'preset_1',
    'harga' => 99000,
    'deskripsi' => 'Pencatatan pengeluaran, penjualan ringkas dari master produk, dan rekap laba kotor.',
    'aktif' => true,
]);
$modulPreset1 = ['pengeluaran', 'master_produk', 'penjualan_ringkas', 'rekap_keuangan'];
$preset1->modul()->sync(Modul::whereIn('kode', $modulPreset1)->pluck('id'));

// Preset 2 — 9 modul (semua Preset 1 + stok)
$preset2 = Paket::create([
    'nama' => 'Paket 2 — POS & Stok',
    'jenis' => 'preset_2',
    'harga' => 199000,
    'deskripsi' => 'Kasir POS dengan auto deduct stok, manajemen stok, alert, opname, dan laporan HPP.',
    'aktif' => true,
]);
$modulPreset2 = array_merge($modulPreset1, ['stok_gudang', 'kasir_pos', 'stock_alert', 'stok_opname', 'laporan_hpp']);
$preset2->modul()->sync(Modul::whereIn('kode', $modulPreset2)->pluck('id'));

// Preset 3 — 13 modul (semua Preset 2 + gudang)
$preset3 = Paket::create([
    'nama' => 'Paket 3 — Gudang',
    'jenis' => 'preset_3',
    'harga' => 299000,
    'deskripsi' => 'Multi-gudang, barang masuk dari supplier, transfer antar gudang, dan kartu stok.',
    'aktif' => true,
]);
$modulPreset3 = array_merge($modulPreset2, ['multi_gudang', 'barang_masuk', 'transfer_gudang', 'kartu_stok']);
$preset3->modul()->sync(Modul::whereIn('kode', $modulPreset3)->pluck('id'));
```

### 5.4 `database/seeders/SuperadminSeeder.php`

```bash
php artisan make:seeder SuperadminSeeder
```

```php
Pengguna::create([
    'nama'     => 'Super Admin',
    'email'    => 'superadmin@saastoko.test',
    'password' => bcrypt('password'),
    'peran'    => 'superadmin',
    'toko_id'  => null,
    'aktif'    => true,
]);
```

### 5.5 Update `DatabaseSeeder`

```php
public function run(): void
{
    $this->call([
        ModulSeeder::class,
        KetergantunganModulSeeder::class,
        PaketSeeder::class,
        SuperadminSeeder::class,
    ]);
}
```

### Jalankan

```bash
php artisan migrate:fresh --seed
```

### ✅ Selesai Jika

```bash
php artisan tinker --execute '
    echo "Modul: " . App\Models\Modul::count() . PHP_EOL;
    echo "Paket: " . App\Models\Paket::count() . PHP_EOL;
    echo "Superadmin: " . App\Models\Pengguna::where("peran","superadmin")->count() . PHP_EOL;
    $kasirPos = App\Models\Modul::where("kode","kasir_pos")->first();
    echo "Dep kasir_pos: " . $kasirPos->ketergantungan->pluck("kode")->join(", ") . PHP_EOL;
'
# Output:
# Modul: 16
# Paket: 3
# Superadmin: 1
# Dep kasir_pos: master_produk, stok_gudang
```

---

## FASE 6: Routes (`routes/web.php`)

Buat struktur route lengkap sesuai pola berikut. Baca kembali `docs/kebutuhan-fungsional.md` sebelum menulis routes untuk memastikan setiap fitur di-gate dengan modul yang benar.

```php
<?php

use App\Http\Controllers\...;
use Illuminate\Support\Facades\Route;

// === PUBLIK ===
Route::get('/', fn () => view('welcome'));

// === AUTH (Breeze — jangan ubah) ===
require __DIR__ . '/auth.php';

// ================================================================
// === SUPERADMIN PANEL ===
// ================================================================
Route::prefix('superadmin')
    ->middleware(['auth', 'peran:superadmin'])
    ->name('superadmin.')
    ->group(function () {
        Route::get('/dashboard', [Superadmin\DashboardController::class, 'index'])->name('dashboard');

        // Manajemen Toko
        Route::resource('toko', Superadmin\TokoController::class);
        Route::post('toko/{toko}/pakai-preset/{paket}', [Superadmin\TokoController::class, 'pakaiPreset'])
            ->name('toko.pakai-preset');

        // Manajemen Modul per Toko
        Route::post('toko/{toko}/modul/{kode}/aktifkan',   [Superadmin\ModulTokoController::class, 'aktifkan'])
            ->name('toko.modul.aktifkan');
        Route::post('toko/{toko}/modul/{kode}/nonaktifkan', [Superadmin\ModulTokoController::class, 'nonaktifkan'])
            ->name('toko.modul.nonaktifkan');

        // CRUD Paket (preset + custom)
        Route::resource('paket', Superadmin\PaketController::class);

        // Verifikasi Pembayaran
        Route::get('verifikasi', [Superadmin\VerifikasiController::class, 'index'])
            ->name('verifikasi.index');
        Route::post('verifikasi/{pembayaran}/setujui', [Superadmin\VerifikasiController::class, 'setujui'])
            ->name('verifikasi.setujui');
        Route::post('verifikasi/{pembayaran}/tolak', [Superadmin\VerifikasiController::class, 'tolak'])
            ->name('verifikasi.tolak');

        // Statistik Platform
        Route::get('statistik', [Superadmin\StatistikController::class, 'index'])
            ->name('statistik');
    });

// ================================================================
// === TENANT PANEL ===
// ================================================================
Route::middleware(['auth', 'verified', 'konteks_toko'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // ── Modul: pengeluaran ──────────────────────────────────
        Route::middleware('modul:pengeluaran')->group(function () {
            Route::resource('pengeluaran', PengeluaranController::class);
        });

        // ── Modul: master_produk ────────────────────────────────
        Route::middleware('modul:master_produk')->group(function () {
            Route::resource('produk', ProdukController::class);
            Route::resource('kategori', KategoriController::class)->except(['show']);
            Route::resource('pemasok', PemasokController::class)->except(['show']);
            Route::get('kasir/cari-produk', [KasirController::class, 'cariProduk'])
                ->name('kasir.cari-produk'); // AJAX endpoint
        });

        // ── Modul: penjualan_ringkas ────────────────────────────
        Route::middleware('modul:penjualan_ringkas')->group(function () {
            Route::resource('penjualan', PenjualanSederhanaController::class)
                ->parameters(['penjualan' => 'penjualanSederhana']);
        });

        // ── Modul: rekap_keuangan ───────────────────────────────
        Route::middleware('modul:rekap_keuangan')->group(function () {
            Route::get('rekap', [RekapController::class, 'index'])->name('rekap.index');
        });

        // ── Modul: kasir_pos ────────────────────────────────────
        Route::middleware('modul:kasir_pos')->group(function () {
            Route::get('kasir', [KasirController::class, 'index'])->name('kasir.index');
            Route::post('kasir/transaksi', [KasirController::class, 'store'])->name('kasir.store');
            Route::get('kasir/riwayat', [KasirController::class, 'riwayat'])->name('kasir.riwayat');
            Route::get('kasir/{transaksi}', [KasirController::class, 'show'])->name('kasir.show');
        });

        // ── Modul: stock_alert ──────────────────────────────────
        Route::middleware('modul:stock_alert')->group(function () {
            Route::get('stok/alert', [StokController::class, 'alert'])->name('stok.alert');
        });

        // ── Modul: stok_opname ──────────────────────────────────
        Route::middleware('modul:stok_opname')->group(function () {
            Route::get('stok/opname', [StokController::class, 'opname'])->name('stok.opname');
            Route::post('stok/opname', [StokController::class, 'simpanOpname'])->name('stok.opname.store');
        });

        // ── Modul: laporan_hpp ──────────────────────────────────
        Route::middleware('modul:laporan_hpp')->group(function () {
            Route::get('laporan/hpp', [LaporanController::class, 'hpp'])->name('laporan.hpp');
        });

        // ── Modul: multi_gudang ─────────────────────────────────
        Route::middleware('modul:multi_gudang')->group(function () {
            Route::resource('gudang', GudangController::class)->except(['show']);
        });

        // ── Modul: barang_masuk ─────────────────────────────────
        Route::middleware('modul:barang_masuk')->group(function () {
            Route::get('gudang/masuk', [BarangMasukController::class, 'index'])->name('barang_masuk.index');
            Route::post('gudang/masuk', [BarangMasukController::class, 'store'])->name('barang_masuk.store');
        });

        // ── Modul: transfer_gudang ──────────────────────────────
        Route::middleware('modul:transfer_gudang')->group(function () {
            Route::get('gudang/transfer', [TransferGudangController::class, 'index'])->name('transfer_gudang.index');
            Route::post('gudang/transfer', [TransferGudangController::class, 'store'])->name('transfer_gudang.store');
        });

        // ── Modul: kartu_stok ───────────────────────────────────
        Route::middleware('modul:kartu_stok')->group(function () {
            Route::get('stok/kartu', [StokController::class, 'kartu'])->name('stok.kartu');
            Route::get('stok/kartu/{produk}', [StokController::class, 'kartuProduk'])->name('stok.kartu.produk');
        });

        // ── Modul: karyawan ─────────────────────────────────────
        Route::middleware('modul:karyawan')->group(function () {
            Route::resource('karyawan', KaryawanController::class);
        });

        // ── Modul: absensi ──────────────────────────────────────
        Route::middleware('modul:absensi')->group(function () {
            Route::get('absensi', [AbsensiController::class, 'index'])->name('absensi.index');
            Route::post('absensi/masuk', [AbsensiController::class, 'masuk'])->name('absensi.masuk');
            Route::post('absensi/keluar', [AbsensiController::class, 'keluar'])->name('absensi.keluar');
            Route::get('absensi/rekap', [AbsensiController::class, 'rekap'])->name('absensi.rekap');
        });

        // ── Modul: payroll ──────────────────────────────────────
        Route::middleware('modul:payroll')->group(function () {
            Route::get('penggajian', [PenggajianController::class, 'index'])->name('penggajian.index');
            Route::post('penggajian/generate', [PenggajianController::class, 'generate'])->name('penggajian.generate');
            Route::get('penggajian/{penggajian}', [PenggajianController::class, 'show'])->name('penggajian.show');
            Route::post('penggajian/{penggajian}/bayar', [PenggajianController::class, 'bayar'])->name('penggajian.bayar');
            Route::get('penggajian/{penggajian}/slip', [PenggajianController::class, 'slip'])->name('penggajian.slip');
        });

        // ── Tagihan/Billing (semua tenant bisa akses) ───────────
        Route::prefix('tagihan')->name('tagihan.')->group(function () {
            Route::get('/', [TagihanController::class, 'index'])->name('index');
            Route::post('/ajukan', [TagihanController::class, 'ajukan'])->name('ajukan');
        });
    });
```

### ✅ Selesai Jika

```bash
php artisan route:list --except-vendor | grep -c "modul:"
# Harus > 15 routes dengan middleware modul

php artisan test --compact
```

---

## FASE 7: Superadmin Panel — Controllers & Views

### Struktur File Controllers

```
app/Http/Controllers/Superadmin/
├── DashboardController.php
├── TokoController.php
├── ModulTokoController.php
├── PaketController.php
├── VerifikasiController.php
└── StatistikController.php
```

```bash
php artisan make:controller Superadmin/DashboardController
php artisan make:controller Superadmin/TokoController --resource
php artisan make:controller Superadmin/ModulTokoController
php artisan make:controller Superadmin/PaketController --resource
php artisan make:controller Superadmin/VerifikasiController
php artisan make:controller Superadmin/StatistikController
```

### Layout Superadmin

**File:** `resources/views/layouts/superadmin.blade.php`

Desain: dark sidebar `#1e293b`, konten putih, font Inter (Google Fonts).

```
┌────────────────────────────────────────────────────────────────┐
│ TOPBAR (#1e293b): 🏪 SaaS Toko Admin    [user] [logout]       │
├───────────────────┬────────────────────────────────────────────┤
│ SIDEBAR (240px)   │                                            │
│ (#1e293b text     │  <main class="p-6">                       │
│  white/gray)      │    @yield('content')                       │
│                   │  </main>                                   │
│ ▸ Dashboard       │                                            │
│ ▸ Kelola Toko     │                                            │
│ ▸ Kelola Paket    │                                            │
│ ▸ Verifikasi      │                                            │
│   Pembayaran      │                                            │
│   [badge merah]   │                                            │
│ ▸ Statistik       │                                            │
└───────────────────┴────────────────────────────────────────────┘
```

### Views Superadmin

```
resources/views/superadmin/
├── dashboard.blade.php
├── paket/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── toko/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php    ← halaman detail toko + toggle modul
├── verifikasi/
│   └── index.blade.php
└── statistik/
    └── index.blade.php
```

### `superadmin/toko/show.blade.php` — Detail + Toggle Modul

Layout tabel modul:
```
┌─────────────────────────────────────────────────────────────────┐
│ Detail Toko: [nama], Paket: [nama paket], Status: [aktif]       │
│ Langganan: [tanggal]     [Ganti Preset ▼]                       │
├─────────────────────────────────────────────────────────────────┤
│ Modul yang Tersedia (16 modul)                                  │
│ ┌──────────────────────┬─────────────────┬────────────────────┐ │
│ │ Nama Modul           │ Requires        │ Status & Aksi      │ │
│ ├──────────────────────┼─────────────────┼────────────────────┤ │
│ │ Kasir POS            │ master_produk,  │ ✅ Aktif           │ │
│ │                      │ stok_gudang     │ [Nonaktifkan]      │ │
│ │ Multi Gudang         │ stok_gudang     │ ❌ Nonaktif        │ │
│ │                      │                 │ [Aktifkan]         │ │
│ │                      │                 │ [Aktifkan + Dep ↗] │ │
│ └──────────────────────┴─────────────────┴────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

Tombol "Aktifkan + Dep" → POST dengan param `dengan_dependency=1` → aktifkan modul beserta semua dependency sekaligus.

### `superadmin/paket/create.blade.php` — Form CRUD Paket

```
┌──────────────────────────────────────────────────────────────┐
│ Buat Paket Baru                                              │
│                                                              │
│ Nama: [_____________________]                                │
│ Jenis: [preset_1 ▼] [preset_2] [preset_3] [custom]          │
│ Harga/bulan: Rp [___________]                                │
│ Deskripsi: [textarea]                                        │
│                                                              │
│ Pilih Modul (centang modul yang termasuk dalam paket):       │
│ ┌────────────────────────────────────────────────────────┐   │
│ │ □ pengeluaran — Pencatatan Pengeluaran                 │   │
│ │ □ master_produk — Master Produk/Kategori/Pemasok       │   │
│ │   □ penjualan_ringkas ↳ requires: master_produk        │   │
│ │     □ rekap_keuangan ↳ requires: penjualan_ringkas     │   │
│ │   □ stok_gudang ↳ requires: master_produk              │   │
│ │     □ kasir_pos ↳ requires: master_produk, stok_gudang │   │
│ │     □ stock_alert ↳ requires: stok_gudang              │   │
│ │     ...                                                │   │
│ └────────────────────────────────────────────────────────┘   │
│                                                              │
│ [Simpan Paket]                                               │
└──────────────────────────────────────────────────────────────┘
```

**JavaScript:** Saat checkbox modul dicentang, otomatis centang semua dependency-nya. Saat di-uncheck, otomatis un-check semua dependen-nya.

### `VerifikasiController` — Logic Setujui

```php
public function setujui(Pembayaran $pembayaran): RedirectResponse
{
    DB::transaction(function () use ($pembayaran) {
        $pembayaran->update([
            'status' => 'disetujui',
            'diverifikasi_oleh' => auth()->id(),
            'diverifikasi_pada' => now(),
        ]);

        $toko = $pembayaran->toko;

        if ($pembayaran->jenis === 'upgrade_paket' && $pembayaran->paket_id) {
            app(ModulService::class)->pakaiPreset($toko, $pembayaran->paket);
            $toko->update(['langganan_berakhir_pada' => now()->addMonth()]);
        } elseif ($pembayaran->jenis === 'aktivasi_addon' && $pembayaran->modul_id) {
            app(ModulService::class)->aktifkanDenganDependency($toko, $pembayaran->modul->kode);
        }
    });

    return back()->with('success', 'Pembayaran disetujui dan modul diaktifkan.');
}
```

### ✅ Selesai Jika

```bash
php artisan test --compact
# Jalankan manual: login superadmin@saastoko.test/password
# Buat toko baru → assign preset 2 → cek modul_toko di DB
php artisan tinker --execute '
    $toko = App\Models\Toko::first();
    echo $toko->modulToko()->where("aktif",true)->count() . " modul aktif\n";
'
# Output: 9 modul aktif (preset 2)
```

---

## FASE 8: Layout & Views Tenant

### Layout Tenant

**File:** `resources/views/layouts/tenant.blade.php`

```
┌────────────────────────────────────────────────────────────────┐
│ TOPBAR: 🏪 [Nama Toko]  [🔔 stok alert]  [user ▼] [logout]   │
├──────────────────┬─────────────────────────────────────────────┤
│ SIDEBAR (220px)  │                                             │
│                  │  <main>                                     │
│ [item menu]      │    @yield('content')                       │
│ (hanya tampil    │  </main>                                   │
│  jika modul      │                                             │
│  aktif)          │                                             │
└──────────────────┴─────────────────────────────────────────────┘
```

**Sidebar dinamis** — contoh cara implementasi:

```blade
{{-- resources/views/components/sidebar-item.blade.php --}}
@props(['route', 'icon', 'label', 'modul' => null])

@if(! $modul || auth()->user()->toko?->modulAktif($modul))
    <a href="{{ route($route) }}"
       class="flex items-center gap-3 px-4 py-2.5 rounded-lg
              {{ request()->routeIs($route.'*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700' }}">
        {!! $icon !!}
        {{ $label }}
    </a>
@endif
```

Daftar sidebar item:
- Dashboard (selalu tampil)
- Pengeluaran (`modul:pengeluaran`)
- Penjualan Ringkas (`modul:penjualan_ringkas`)
- Kasir POS (`modul:kasir_pos`)
- Produk/Master (`modul:master_produk`)
- Stok & Gudang (`modul:stok_gudang`)
- Multi Gudang (`modul:multi_gudang`)
- Karyawan (`modul:karyawan`)
- Absensi (`modul:absensi`)
- Penggajian (`modul:payroll`)
- Tagihan (selalu tampil)

### View Kasir POS (`resources/views/kasir/index.blade.php`)

UI kasir dua panel:

```
┌───────────────────┬──────────────────────────────────────────┐
│ PANEL KIRI        │ PANEL KANAN: KERANJANG                   │
│ Gudang: [select]  │ ─────────────────────────────────────── │
│                   │ Item 1: Produk A | 2 pcs | Rp 50.000    │
│ Cari Produk:      │ Item 2: Produk B | 1 pcs | Rp 30.000    │
│ [______________]  │ [x hapus]                                │
│                   │ ─────────────────────────────────────── │
│ Hasil:            │ Subtotal : Rp 130.000                   │
│ ┌ Produk A Rp25k  │ Diskon   : Rp [_______]                 │
│ │ Stok: 10        │ Total    : Rp 130.000                   │
│ └ [+ Tambah]      │ Bayar    : Rp [_______]                 │
│                   │ Kembalian: Rp 0                         │
│ ┌ Produk B Rp30k  │ Metode: ◉ Tunai ○ QRIS ○ Transfer      │
│ │ Stok: 5         │                                          │
│ └ [+ Tambah]      │ [🛒 Bayar Sekarang]                     │
└───────────────────┴──────────────────────────────────────────┘
```

Implementasi AJAX:
1. Saat ketik di search box → `GET /kasir/cari-produk?q=...` → return JSON `[{id, nama, harga_jual, stok}]`
2. Saat klik "+ Tambah" → masukkan ke keranjang (state di JS, bukan form biasa)
3. Validasi qty > stok → tampilkan error inline merah di item
4. Submit → POST `/kasir/transaksi` dengan JSON body

### View Absensi (`resources/views/absensi/index.blade.php`)

Mobile-first, diakses dari HP:

```
┌──────────────────────────────────────┐
│ Selamat Pagi, [Nama Karyawan]        │
│ Senin, 27 Agustus 2026 · 08:00 WIB  │
│                                      │
│ ⊙ Lokasi: Terdeteksi ✓              │
│   [nama jalan / koordinat]           │
│   Jarak dari toko: 45m              │
│                                      │
│ Status Hari Ini:                     │
│ ○ Belum Clock In                    │
│   Jam masuk: --                     │
│                                      │
│ ┌────────────────────────────────┐   │
│ │  📷 Ambil Selfie & Clock In   │   │
│ └────────────────────────────────┘   │
│                                      │
│ ── Riwayat 5 Hari Terakhir ────────  │
│ Senin  08:02 - 17:00 ✓ Tepat Waktu  │
│ Selasa 08:15 - 17:30 ⚠ Telat 15m   │
└──────────────────────────────────────┘
```

JavaScript yang dibutuhkan:
- `navigator.geolocation.getCurrentPosition()` untuk dapatkan koordinat
- Hitung jarak preview sebelum submit (opsional)
- Kamera API untuk foto selfie: `navigator.mediaDevices.getUserMedia`
- POST ke `/absensi/masuk` dengan JSON: `{lintang, bujur, foto_base64}`

---

## FASE 9: Controllers Tenant

```bash
php artisan make:controller DashboardController
php artisan make:controller PengeluaranController --resource
php artisan make:controller ProdukController --resource
php artisan make:controller KategoriController --resource
php artisan make:controller PemasokController --resource
php artisan make:controller PenjualanSederhanaController --resource
php artisan make:controller RekapController
php artisan make:controller KasirController
php artisan make:controller StokController
php artisan make:controller GudangController --resource
php artisan make:controller BarangMasukController
php artisan make:controller TransferGudangController
php artisan make:controller LaporanController
php artisan make:controller KaryawanController --resource
php artisan make:controller AbsensiController
php artisan make:controller PenggajianController
php artisan make:controller TagihanController
```

### Form Request untuk Validasi

```bash
php artisan make:request StorePengeluaranRequest
php artisan make:request StoreProdukRequest
php artisan make:request StorePenjualanSederhanaRequest
php artisan make:request StoreTransaksiRequest
php artisan make:request StoreKaryawanRequest
php artisan make:request StoreAbsensiMasukRequest
php artisan make:request GeneratePenggajianRequest
php artisan make:request AjukanTagihanRequest
```

### `KasirController@store` — Logic Kritis

```php
public function store(StoreTransaksiRequest $request): RedirectResponse
{
    DB::transaction(function () use ($request) {
        $gudang = Gudang::withoutGlobalScope('toko')->findOrFail($request->gudang_id);

        $transaksi = Transaksi::create([
            'pengguna_id'        => auth()->id(),
            'gudang_id'          => $gudang->id,
            'tanggal_transaksi'  => today(),
            'subtotal'           => $request->subtotal,
            'diskon'             => $request->diskon ?? 0,
            'total'              => $request->total,
            'jumlah_bayar'       => $request->jumlah_bayar,
            'kembalian'          => $request->jumlah_bayar - $request->total,
            'metode_pembayaran'  => $request->metode_pembayaran,
        ]);

        foreach ($request->items as $item) {
            $produk = Produk::findOrFail($item['produk_id']);

            // Deduct stok — throws Exception jika tidak cukup
            app(StokService::class)->kurangiStok(
                $produk, $gudang, $item['jumlah'],
                Transaksi::class, $transaksi->id,
                'Transaksi kasir #' . $transaksi->id
            );

            ItemTransaksi::create([
                'transaksi_id'       => $transaksi->id,
                'produk_id'          => $produk->id,
                'nama_produk'        => $produk->nama,
                'jumlah'             => $item['jumlah'],
                'harga_satuan'       => $item['harga_satuan'],
                'subtotal'           => $item['jumlah'] * $item['harga_satuan'],
                'harga_beli_snapshot'=> $produk->harga_beli,
            ]);
        }
    });

    return redirect()->route('kasir.riwayat')->with('success', 'Transaksi berhasil disimpan.');
}
```

### `AbsensiController` — Haversine + Foto

```php
public function masuk(StoreAbsensiMasukRequest $request): JsonResponse
{
    $toko      = auth()->user()->toko;
    $karyawan  = auth()->user()->karyawan;
    $lintang   = (float) $request->lintang;
    $bujur     = (float) $request->bujur;

    // Validasi lokasi
    if ($toko->garis_lintang && $toko->garis_bujur) {
        $jarak = $this->hitungJarak($lintang, $bujur, (float) $toko->garis_lintang, (float) $toko->garis_bujur);
        if ($jarak > $toko->radius_absensi) {
            return response()->json([
                'error' => "Anda berada {$jarak}m dari toko. Maksimal radius: {$toko->radius_absensi}m."
            ], 422);
        }
    }

    // Simpan foto (base64 → storage)
    $fotoPath = null;
    if ($request->foto_base64) {
        $foto = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $request->foto_base64));
        $fotoPath = 'absensi/' . $karyawan->id . '_' . now()->format('Ymd_His') . '.jpg';
        Storage::disk('public')->put($fotoPath, $foto);
    }

    $jamMasukSeharusnya = now()->copy()->setTime(8, 0);
    $telat = now()->gt($jamMasukSeharusnya);

    Absensi::updateOrCreate(
        ['toko_id' => $toko->id, 'karyawan_id' => $karyawan->id, 'tanggal' => today()],
        [
            'jam_masuk'      => now(),
            'lintang_masuk'  => $lintang,
            'bujur_masuk'    => $bujur,
            'foto_masuk'     => $fotoPath,
            'status'         => $telat ? 'telat' : 'tepat_waktu',
            'menit_telat'    => $telat ? now()->diffInMinutes($jamMasukSeharusnya) : 0,
        ]
    );

    return response()->json(['success' => true, 'status' => $telat ? 'telat' : 'tepat_waktu']);
}

private function hitungJarak(float $lat1, float $lon1, float $lat2, float $lon2): int
{
    $r = 6371000; // radius bumi dalam meter
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
    return (int) round($r * 2 * atan2(sqrt($a), sqrt(1 - $a)));
}
```

---

## FASE 10: Feature Tests

### Struktur Test

```
tests/Feature/
├── Auth/                     ← sudah ada (Breeze), jangan diubah
├── ModulServiceTest.php
├── SuperadminPaketTest.php
├── SuperadminTokoTest.php
├── VerifikasiPembayaranTest.php
├── TenantIsolationTest.php   ← WAJIB
├── PengeluaranTest.php
├── ProdukTest.php
├── PenjualanSederhanaTest.php
├── KasirPosTest.php
├── StokTest.php
├── KaryawanTest.php
├── AbsensiTest.php
├── PenggajianTest.php
└── TagihanTest.php
```

```bash
# Buat semua file test
php artisan make:test ModulServiceTest --phpunit
php artisan make:test SuperadminPaketTest --phpunit
php artisan make:test SuperadminTokoTest --phpunit
php artisan make:test VerifikasiPembayaranTest --phpunit
php artisan make:test TenantIsolationTest --phpunit
php artisan make:test PengeluaranTest --phpunit
php artisan make:test ProdukTest --phpunit
php artisan make:test PenjualanSederhanaTest --phpunit
php artisan make:test KasirPosTest --phpunit
php artisan make:test StokTest --phpunit
php artisan make:test KaryawanTest --phpunit
php artisan make:test AbsensiTest --phpunit
php artisan make:test PenggajianTest --phpunit
php artisan make:test TagihanTest --phpunit
```

### Helper `setUp` yang Reusable

Buat trait test agar tidak duplikat kode setup:

**File:** `tests/Feature/Concerns/WithTokoSetup.php`

```php
<?php

namespace Tests\Feature\Concerns;

use App\Models\Modul;
use App\Models\Paket;
use App\Models\Pengguna;
use App\Models\Toko;
use App\Services\ModulService;
use Database\Seeders\KetergantunganModulSeeder;
use Database\Seeders\ModulSeeder;
use Database\Seeders\PaketSeeder;

trait WithTokoSetup
{
    protected Toko $toko;
    protected Pengguna $admin;
    protected Pengguna $superadmin;

    protected function buatTokoPreset(string $jenis = 'preset_1'): void
    {
        $this->seed(ModulSeeder::class);
        $this->seed(KetergantunganModulSeeder::class);
        $this->seed(PaketSeeder::class);

        $paket = Paket::where('jenis', $jenis)->first();

        $this->toko = Toko::factory()->create(['paket_id' => $paket->id]);
        $this->admin = Pengguna::factory()->create([
            'toko_id' => $this->toko->id,
            'peran' => 'admin',
        ]);

        // Aktifkan modul dari preset
        app(ModulService::class)->pakaiPreset($this->toko, $paket);
    }

    protected function aktifkanModul(string ...$kode): void
    {
        foreach ($kode as $k) {
            app(ModulService::class)->aktifkan($this->toko, $k);
        }
    }
}
```

### Template Test (ikuti pola ini untuk semua test)

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\WithTokoSetup;
use Tests\TestCase;

class PengeluaranTest extends TestCase
{
    use RefreshDatabase, WithTokoSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buatTokoPreset('preset_1');
        // modul pengeluaran sudah aktif di preset_1
    }

    public function test_admin_bisa_lihat_halaman_pengeluaran(): void
    {
        $response = $this->actingAs($this->admin)->get(route('pengeluaran.index'));
        $response->assertOk();
        $response->assertViewIs('pengeluaran.index');
    }

    public function test_admin_bisa_tambah_pengeluaran(): void
    {
        $response = $this->actingAs($this->admin)->post(route('pengeluaran.store'), [
            'tanggal_pengeluaran' => today()->toDateString(),
            'keterangan' => 'Beli bahan baku',
            'nominal' => 150000,
        ]);

        $response->assertRedirect(route('pengeluaran.index'));
        $this->assertDatabaseHas('pengeluaran', [
            'toko_id' => $this->toko->id,
            'keterangan' => 'Beli bahan baku',
            'nominal' => 150000,
        ]);
    }

    public function test_halaman_ditolak_jika_modul_nonaktif(): void
    {
        // Nonaktifkan modul pengeluaran
        app(\App\Services\ModulService::class)->nonaktifkan($this->toko, 'pengeluaran');

        $response = $this->actingAs($this->admin)->get(route('pengeluaran.index'));
        $response->assertRedirect(route('dashboard'));
    }

    public function test_data_toko_lain_tidak_tampil(): void
    {
        $tokoLain = \App\Models\Toko::factory()->create(['paket_id' => $this->toko->paket_id]);
        \App\Models\Pengeluaran::factory()->create(['toko_id' => $tokoLain->id]);

        $response = $this->actingAs($this->admin)->get(route('pengeluaran.index'));
        $response->assertOk();
        // Pastikan data toko lain tidak muncul
        $response->assertDontSee($tokoLain->nama);
    }
}
```

### Test Wajib: `ModulServiceTest`

Minimal test cases:

```php
public function test_tidak_bisa_aktifkan_modul_tanpa_dependency(): void
{
    // kasir_pos butuh master_produk + stok_gudang
    $this->expectException(ValidationException::class);
    app(ModulService::class)->aktifkan($this->toko, 'kasir_pos');
}

public function test_aktifkan_dengan_dependency_mengaktifkan_semua_modul_yang_diperlukan(): void
{
    app(ModulService::class)->aktifkanDenganDependency($this->toko, 'kasir_pos');

    $this->assertTrue($this->toko->fresh()->modulAktif('kasir_pos'));
    $this->assertTrue($this->toko->fresh()->modulAktif('master_produk'));
    $this->assertTrue($this->toko->fresh()->modulAktif('stok_gudang'));
}

public function test_tidak_bisa_nonaktifkan_modul_yang_masih_punya_dependen_aktif(): void
{
    app(ModulService::class)->aktifkanDenganDependency($this->toko, 'kasir_pos');

    $this->expectException(ValidationException::class);
    // stok_gudang tidak bisa nonaktif karena kasir_pos masih aktif
    app(ModulService::class)->nonaktifkan($this->toko, 'stok_gudang');
}

public function test_pakai_preset_mengaktifkan_semua_modul_preset(): void
{
    $preset2 = Paket::where('jenis', 'preset_2')->first();
    app(ModulService::class)->pakaiPreset($this->toko, $preset2);

    // Preset 2 punya 9 modul
    $this->assertSame(9, $this->toko->modulToko()->where('aktif', true)->count());
}
```

### Test Wajib: `TenantIsolationTest`

```php
public function test_global_scope_mencegah_kebocoran_data_antar_toko(): void
{
    $tokoB = Toko::factory()->create(['paket_id' => $this->toko->paket_id]);
    $dataTokoBid = \App\Models\Pengeluaran::factory()
        ->create(['toko_id' => $tokoB->id, 'keterangan' => 'Data Rahasia Toko B']);

    // Login sebagai admin toko A
    $this->actingAs($this->admin);

    // Global scope harus filter by toko_id — tidak boleh dapat data toko B
    $pengeluaran = \App\Models\Pengeluaran::all();
    $this->assertTrue($pengeluaran->every(fn ($p) => $p->toko_id === $this->toko->id));
    $this->assertFalse($pengeluaran->contains('id', $dataTokoBid->id));
}
```

### Test Wajib: `KasirPosTest` — Validasi Stok

```php
public function test_kasir_tidak_bisa_jual_melebihi_stok(): void
{
    // Setup: produk dengan stok 2
    $gudang  = \App\Models\Gudang::factory()->create(['toko_id' => $this->toko->id]);
    $produk  = \App\Models\Produk::factory()->create(['toko_id' => $this->toko->id]);
    \App\Models\StokGudang::create([
        'toko_id' => $this->toko->id,
        'produk_id' => $produk->id,
        'gudang_id' => $gudang->id,
        'jumlah' => 2,
    ]);

    // Coba jual 5 (melebihi stok 2)
    $response = $this->actingAs($this->admin)->post(route('kasir.store'), [
        'gudang_id' => $gudang->id,
        'items' => [
            ['produk_id' => $produk->id, 'jumlah' => 5, 'harga_satuan' => 10000],
        ],
        'subtotal' => 50000, 'diskon' => 0, 'total' => 50000,
        'jumlah_bayar' => 50000, 'metode_pembayaran' => 'tunai',
    ]);

    $response->assertSessionHasErrors();
    // Stok harus tetap 2
    $this->assertDatabaseHas('stok_gudang', ['produk_id' => $produk->id, 'jumlah' => 2]);
}
```

---

## Verifikasi Akhir

```bash
# 1. Fresh database + seed
php artisan migrate:fresh --seed

# 2. Run semua test
php artisan test --compact
# Target: ≥ 80 test, ≥ 250 assertions, semua PASS

# 3. Pint check
vendor/bin/pint --dirty
# Output: No files with issues

# 4. Build frontend
npm run build
# Output: vite build sukses, tidak ada error

# 5. Cek jumlah routes
php artisan route:list --except-vendor | wc -l
# Harus > 60 routes
```

---

## Hal yang Perlu Dikonfirmasi ke User Sebelum atau Saat Eksekusi

1. **Jam kerja absensi** — Default 08:00 WIB. Apakah perlu dikonfigurasi per toko?
2. **Durasi langganan per pembayaran** — Default 1 bulan setelah verifikasi. Berbeda per paket?
3. **Storage foto absensi** — Local disk (public) atau cloud (S3)?
4. **Notifikasi email** — Perlu email saat pembayaran diverifikasi/ditolak?
5. **Hitung lembur** — Jam kerja selesai jam berapa? Otomatis dari jam keluar?
6. **Upload bukti transfer** — Max file size? Format yang diterima (jpg/png/pdf)?

