<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\AddonToko;
use App\Models\Toko;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AddonController extends Controller
{
    public function index(): View
    {
        return view('superadmin.addon.index', [
            'addon' => Addon::all(),
        ]);
    }

    public function create(): View
    {
        return view('superadmin.addon.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kode' => ['required', 'alpha_dash', 'unique:addon,kode'],
            'nama' => ['required', 'string', 'max:255'],
            'harga' => ['required', 'numeric', 'min:0'],
            'aktif' => ['boolean'],
        ]);

        Addon::create($data);

        return redirect()->route('superadmin.addon.index')->with('status', 'Add-on ditambahkan.');
    }

    public function edit(Addon $addon): View
    {
        return view('superadmin.addon.edit', ['addon' => $addon]);
    }

    public function update(Request $request, Addon $addon): RedirectResponse
    {
        $data = $request->validate([
            'kode' => ['required', 'alpha_dash', Rule::unique('addon', 'kode')->ignore($addon->id)],
            'nama' => ['required', 'string', 'max:255'],
            'harga' => ['required', 'numeric', 'min:0'],
            'aktif' => ['boolean'],
        ]);

        $addon->update($data);

        return redirect()->route('superadmin.addon.index')->with('status', 'Add-on diperbarui.');
    }

    public function toggleToko(Request $request, Toko $toko, Addon $addon): RedirectResponse
    {
        $pivot = AddonToko::where('toko_id', $toko->id)->where('addon_id', $addon->id)->first();

        if ($pivot) {
            $pivot->update(['aktif' => ! $pivot->aktif]);
            $status = $pivot->aktif ? 'diaktifkan' : 'dinonaktifkan';
        } else {
            AddonToko::create([
                'toko_id' => $toko->id,
                'addon_id' => $addon->id,
                'aktif' => true,
                'diaktifkan_pada' => now(),
            ]);
            $status = 'diaktifkan';
        }

        return back()->with('status', 'Add-on "'.$addon->nama.'" '.$status.' untuk toko "'.$toko->nama.'".');
    }
}
