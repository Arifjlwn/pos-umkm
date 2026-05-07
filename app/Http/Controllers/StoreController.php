<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoreController extends Controller
{
    // Menampilkan halaman form Setup Toko
    public function create()
    {
        return Inertia::render('SetupToko');
    }

    // Memproses data form yang dikirim user
    public function store(Request $request)
    {
        // 1. Validasi inputan
        $request->validate([
            'nama_toko' => 'required|string|max:255',
            'alamat_toko' => 'required|string',
            'telepon' => 'nullable|string|max:20',
        ]);

        // 2. Simpan data toko ke database
        $store = Store::create([
            'nama_toko' => $request->nama_toko,
            'alamat_toko' => $request->alamat_toko,
            'telepon' => $request->telepon,
            // Kasih default fitur 'kasir' untuk awalan
            'fitur_opsional' => ['kasir'],
        ]);

        // 3. Update data User yang sedang login
        // Ikatkan dia ke toko yang baru dibuat, dan pastikan jabatannya 'owner'
        $request->user()->update([
            'store_id' => $store->id,
            'role' => 'owner'
        ]);

        // 4. Buka gembok dan arahkan ke mesin POS!
        return redirect()->route('pos');
    }
}
