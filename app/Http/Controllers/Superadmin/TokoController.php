<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Paket;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TokoController extends Controller
{
    public function index(): View
    {
        return view('superadmin.toko.index', [
            'toko' => Toko::with(['paket', 'admin'])->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('superadmin.toko.create', [
            'paket' => Paket::orderBy('tingkat')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);

        $toko = DB::transaction(function () use ($data) {
            $tokoBaru = Toko::create([
                ...$data,
                'slug' => $this->buatSlug($data['nama']),
                'langganan_berakhir_pada' => now()->addMonth(),
            ]);

            $admin = User::create([
                'toko_id' => $tokoBaru->id,
                'name' => $data['nama_admin'],
                'email' => $data['email_admin'],
                'password' => bcrypt($data['password_admin'] ?? 'password'),
                'peran' => 'admin',
                'aktif' => true,
            ]);

            $admin->update(['dibuat_oleh' => auth()->id()]);

            return $tokoBaru;
        });

        return redirect()->route('superadmin.toko.index')
            ->with('status', 'Toko "'.$toko->nama.'" berhasil didaftarkan beserta akun admin-nya.');
    }

    public function edit(Toko $toko): View
    {
        return view('superadmin.toko.edit', [
            'toko' => $toko,
            'paket' => Paket::orderBy('tingkat')->get(),
        ]);
    }

    public function update(Request $request, Toko $toko): RedirectResponse
    {
        $toko->update($this->validasi($request));

        return redirect()->route('superadmin.toko.index')
            ->with('status', 'Data toko "'.$toko->nama.'" diperbarui.');
    }

    public function destroy(Toko $toko): RedirectResponse
    {
        $toko->delete();

        return redirect()->route('superadmin.toko.index')
            ->with('status', 'Toko "'.$toko->nama.'" dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validasi(Request $request): array
    {
        $aturanEmail = $request->isMethod('post')
            ? ['required', 'email', 'unique:pengguna,email']
            : ['nullable', 'email'];

        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'paket_id' => ['required', 'exists:paket,id'],
            'status' => ['required', 'in:coba_gratis,aktif,nonaktif'],
            'garis_lintang' => ['nullable', 'numeric', 'between:-90,90'],
            'garis_bujur' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_absensi' => ['nullable', 'integer', 'min:10', 'max:5000'],
            'langganan_berakhir_pada' => ['nullable', 'date'],
            'nama_admin' => [$request->isMethod('post') ? 'required' : 'nullable', 'string', 'max:255'],
            'email_admin' => $aturanEmail,
            'password_admin' => ['nullable', 'string', 'min:8'],
        ]);
    }

    private function buatSlug(string $nama): string
    {
        $slug = $dasar = str($nama)->slug()->value() ?: str()->random(8);
        $i = 1;

        while (Toko::where('slug', $slug)->exists()) {
            $slug = $dasar.'-'.++$i;
        }

        return (string) $slug;
    }
}
