<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Paket;
use App\Models\Toko;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('superadmin.dashboard', [
            'jumlahToko' => Toko::count(),
            'tokoAktif' => Toko::where('status', 'aktif')->count(),
            'jumlahPengguna' => User::count(),
            'paket' => Paket::withCount('toko')->orderBy('tingkat')->get(),
            'addon' => Addon::all(),
        ]);
    }
}
