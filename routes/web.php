<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;



Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/pos', function () {
    return Inertia::render('Pos/Index', [
        'products' => Product::all()
    ]);
});

Route::post('/pos/checkout', function (Request $request) {
    // 1. Bungkus dengan DB Transaction agar aman (kalo error tengah jalan, data di rollback)
    DB::transaction(function () use ($request) {

        // 2. Simpan ke tabel master 'transactions'
        $transaction = Transaction::create([
            'total_price' => $request->total_price,
            'pay_amount' => $request->pay_amount,
            'return_amount' => $request->pay_amount - $request->total_price,
        ]);

        // 3. Looping isi keranjang dan simpan ke 'transaction_details'
        foreach ($request->cart as $item) {
            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'product_id' => $item['id'],
                'quantity' => $item['qty'],
                'price' => $item['price'],
            ]);

            // 4. Potong Stok barang otomatis
            $product = Product::find($item['id']);
            $product->decrement('stock', $item['qty']);
        }
    });

    // 5. Kembalikan ke halaman awal dengan pesan sukses
    return redirect()->back();
});

Route::resource('products', ProductController::class);

require __DIR__ . '/auth.php';
