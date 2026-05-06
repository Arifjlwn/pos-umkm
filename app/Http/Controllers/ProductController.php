<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Inertia\Inertia;

class ProductController extends Controller
{
    // Menampilkan Halaman Master Produk
    public function index()
    {
        return Inertia::render('Products/Index', [
            'products' => Product::latest()->get()
        ]);
    }

    // Menyimpan Produk Baru Beserta Gambar
    public function store(Request $request)
    {
        // Validasi Inputan
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maks 2MB
        ]);

        $imagePath = null;

        // Cek apakah ada file di upload
        if ($request->hasFile('image')) {
            // Simpan ke folder 'storage/app/public/products'
            $path = $request->file('image')->store('products', 'public');
            // Jadikan URL public untuk disimpan di DB
            $imagePath = '/storage/' . $path;
        }

        // Masukan ke database
        Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $imagePath,
        ]);

        return redirect()->back();
    }

    // Update Barang Beserta Gambar
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maks 2MB
        ]);

        $imagePath = $product->image; // Pertahankan gambar lama jika tidak ada yang baru

        // Jika ada gambar baru, simpan dan hapus gambar lama
        if ($request->hasFile('image')) {
            // Hapus gambar lama
            if ($product->image && str_starts_with($product->image, '/storage/')) {
                Storage::disk('public') -> delete(str_replace('/storage/','',$product->image));
            }

            // Simpan gambar baru
            $path = $request->file('image')->store('products', 'public');
            $imagePath = '/storage/' . $path;
        }

        // Update data di database
        $product->update([
            'name' => $request->name,
            'price'=> $request->price,
            'stock' => $request->stock,
            'image' => $imagePath,
        ]);

        return redirect()->back();
    }

    // Fungsi Hapus Barang
    public function destroy(Product $product)
    {
        try {
            // Coba hapus data dari database DULU
            $product->delete();

            // JIKA BERHASIL: baru hapus foto fisiknya dari folder
            if ($product->image && str_starts_with($product->image, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $product->image));
            }
            
            return redirect()->back();

        } catch (QueryException $e) {
            // JIKA GAGAL KARENA FOREIGN KEY: Tangkap errornya dan kirim pesan ke Vue
            return redirect()->back()->withErrors([
                'message' => 'GAGAL: Produk ini tidak bisa dihapus karena sudah tercatat di dalam riwayat transaksi (struk)!'
            ]);
        }
    }
}
