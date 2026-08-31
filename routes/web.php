<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrakController;
use App\Http\Controllers\BuyurtmaController;
use App\Http\Controllers\ChevarController;
use App\Http\Controllers\ChiqimController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\XomashyoController;
use App\Http\Controllers\YoqotishController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::get('/kirish', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/kirish', [AuthController::class, 'login'])->name('login.store');
Route::post('/chiqish', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Companiyalar (auth kerak, company middleware YO'Q —
| super_admin companiya tanlamasa ham kira oladi)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::post('companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::get('companies/blocked', [CompanyController::class, 'blocked'])->name('companies.blocked');
    Route::get('companies/{company}/select', [CompanyController::class, 'select'])->name('companies.select');
    Route::put('companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
    Route::put('companies/{company}/extend', [CompanyController::class, 'extend'])->name('companies.extend');
    Route::put('companies/{company}/toggle-block', [CompanyController::class, 'toggleBlock'])->name('companies.toggleBlock');
    Route::delete('companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
    Route::post('companies/{company}/users', [CompanyController::class, 'storeUser'])->name('companies.users.store');
    Route::delete('companies/{company}/users/{user}', [CompanyController::class, 'detachUser'])->name('companies.users.destroy');
});

/*
|--------------------------------------------------------------------------
| Asosiy admin (auth + company konteksti)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'company'])->group(function () {

    // Dashboard — root
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {

        // Mahsulotlar
        Route::resource('products', ProductsController::class)
            ->parameters(['products' => 'product'])
            ->except(['show']);

        Route::post('products/produce', [ProductsController::class, 'produce'])->name('products.produce');
        Route::post('products/sell', [ProductsController::class, 'sell'])->name('products.sell');

        // Xomashyolar
        Route::resource('xomashyolar', XomashyoController::class)
            ->parameters(['xomashyolar' => 'xomashyo'])
            ->only(['store', 'update', 'destroy']);

        // Chiqim / yo'qotish / brak
        Route::post('chiqimlar', [ChiqimController::class, 'store'])->name('chiqimlar.store');
        Route::delete('chiqimlar/{chiqim}', [ChiqimController::class, 'destroy'])->name('chiqimlar.destroy');

        Route::post('yoqotishlar', [YoqotishController::class, 'store'])->name('yoqotishlar.store');
        Route::delete('yoqotishlar/{yoqotish}', [YoqotishController::class, 'destroy'])->name('yoqotishlar.destroy');

        Route::post('braklar', [BrakController::class, 'store'])->name('braklar.store');
        Route::put('braklar/{brak}', [BrakController::class, 'update'])->name('braklar.update');
        Route::delete('braklar/{brak}', [BrakController::class, 'destroy'])->name('braklar.destroy');

        // Xodimlar (chevar)
        Route::get('xodimlar', [ChevarController::class, 'index'])->name('xodimlar.index');
        Route::post('xodimlar', [ChevarController::class, 'storeChevar'])->name('xodimlar.store');
        Route::put('xodimlar/{user}', [ChevarController::class, 'updateChevar'])->name('xodimlar.update');
        Route::delete('xodimlar/{user}', [ChevarController::class, 'destroyChevar'])->name('xodimlar.destroy');

        Route::post('chevar-ishlari', [ChevarController::class, 'storeIsh'])->name('chevar-ishlari.store');
        Route::put('chevar-ishlari/{ish}', [ChevarController::class, 'updateIsh'])->name('chevar-ishlari.update');
        Route::delete('chevar-ishlari/{ish}', [ChevarController::class, 'destroyIsh'])->name('chevar-ishlari.destroy');

        Route::post('chevar-tolovlar', [ChevarController::class, 'storeTolov'])->name('chevar-tolovlar.store');
        Route::put('chevar-tolovlar/{tolov}', [ChevarController::class, 'updateTolov'])->name('chevar-tolovlar.update');
        Route::delete('chevar-tolovlar/{tolov}', [ChevarController::class, 'destroyTolov'])->name('chevar-tolovlar.destroy');

        // Sotuvlar
        Route::put('sotuvlar/{sotuv}', [ProductsController::class, 'updateSell'])->name('sotuvlar.update');
        Route::delete('sotuvlar/{sotuv}', [ProductsController::class, 'destroySell'])->name('sotuvlar.destroy');

        // Buyurtmalar  ← TO'G'RI: prefix/name allaqachon admin.
        // URL:  /admin/buyurtmalar
        // name: admin.buyurtmalar.index
        Route::get('buyurtmalar', [BuyurtmaController::class, 'index'])->name('buyurtmalar.index');
        Route::post('buyurtmalar', [BuyurtmaController::class, 'store'])->name('buyurtmalar.store');
        Route::put('buyurtmalar/{buyurtma}', [BuyurtmaController::class, 'update'])->name('buyurtmalar.update');
        Route::put('buyurtmalar/{buyurtma}/holat', [BuyurtmaController::class, 'updateHolat'])->name('buyurtmalar.holat');
        Route::delete('buyurtmalar/{buyurtma}', [BuyurtmaController::class, 'destroy'])->name('buyurtmalar.destroy');
    });
});
