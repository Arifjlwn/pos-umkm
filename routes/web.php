<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;

// Rute awal langsung arahkan ke login saja biar tidak bingung
Route::get('/', function () {
    return redirect('/login');
});

// --- SEMUA RUTE DI BAWAH INI WAJIB LOGIN ---
Route::middleware(['auth', 'verified'])->group(function () {

    // Rute Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Rute Setup Toko
    Route::get('/setup-toko', [StoreController::class, 'create'])->name('setup.toko');
    Route::post('/setup-toko', [StoreController::class, 'store'])->name('setup.toko.store');

    // 1. Rute POS
    Route::get('/pos', function () {
        return Inertia::render('Pos/Index', [
            'products' => Product::all()
        ]);
    })->name('pos');

    // 2. Rute Checkout Transaksi
    Route::post('/pos/checkout', function (Request $request) {
        DB::transaction(function () use ($request) {
            $transaction = Transaction::create([
                'total_price' => $request->total_price,
                'pay_amount' => $request->pay_amount,
                'payment_method' => $request->payment_method,
                'return_amount' => $request->pay_amount - $request->total_price,
            ]);

            foreach ($request->cart as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                ]);

                $product = Product::find($item['id']);
                $product->decrement('stock', $item['qty']);
            }
        });
        return redirect()->back();
    });

    // 3. Rute Produk
    Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
    Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
    Route::resource('/products', ProductController::class);

}); // Penutup middleware auth

require __DIR__ . '/auth.php';
