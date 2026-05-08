<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        // Ambil semua data user (Owner & Kasir) yang bekerja di toko yang sama
        $karyawan = User::where('store_id', $request->user()->store_id)->get();

        return Inertia::render('Absensi/Index', [
            'karyawan' => $karyawan
        ]);
    }
}
