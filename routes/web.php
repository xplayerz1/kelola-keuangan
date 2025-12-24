<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes - HMSI Finance Management System
|--------------------------------------------------------------------------
*/

// =================== PUBLIC ROUTES ===================
Route::get('/', function () {
    return redirect('/dashboard');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout Route (harus authenticated)
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// =================== PUBLIC API ROUTES ===================
// Indonesian Address API - For Registration (No Auth Required)
Route::get('/api/provinces', [AdminController::class, 'getProvinces'])->name('api.provinces.public');
Route::get('/api/cities', [AdminController::class, 'getCities'])->name('api.cities.public');

// =================== AUTHENTICATED ROUTES ===================
Route::middleware(['auth'])->group(function () {
    
    // Dashboard - Accessible by all authenticated users
    Route::get('/dashboard', function () {
        // Get active saldo data
        $aktiveSaldo = \App\Models\MasterSaldo::where('status', 'aktif')->first();
        
        // Calculate statistics
        $totalPemasukan = 0;
        $totalPengeluaran = 0;
        $saldoAkhir = 0;
        
        if ($aktiveSaldo) {
            // IMPORTANT: Column names are total_masuk & total_keluar (not total_pemasukan/pengeluaran)
            $totalPemasukan = $aktiveSaldo->total_masuk ?? 0;
            $totalPengeluaran = $aktiveSaldo->total_keluar ?? 0;
            $saldoAkhir = $aktiveSaldo->saldo_akhir ?? 0;
        }
        
        // Get recent transactions
        $recentTransactions = \App\Models\Transaction::with(['category', 'user'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('dashboard', compact('totalPemasukan', 'totalPengeluaran', 'saldoAkhir', 'recentTransactions'));
    })->name('dashboard');

    // =================== ADMIN ONLY ROUTES ===================
    // Middleware: checkrole:Admin
    Route::middleware(['checkrole:Admin'])->prefix('admin')->group(function () {
        
        // User Management (Orang 1)
        Route::resource('users', AdminController::class)->names([
            'index' => 'admin.users.index',
            'create' => 'admin.users.create',
            'store' => 'admin.users.store',
            'show' => 'admin.users.show',
            'edit' => 'admin.users.edit',
            'update' => 'admin.users.update',
            'destroy' => 'admin.users.destroy',
        ]);
        
        // Custom route untuk change role
        Route::put('users/{id}/change-role', [AdminController::class, 'changeRole'])
            ->name('admin.users.change-role');
        
        // API Routes untuk AJAX (Indonesian Address API)
        Route::prefix('api')->group(function () {
            Route::get('provinces', [AdminController::class, 'getProvinces'])->name('api.provinces');
            Route::get('cities', [AdminController::class, 'getCities'])->name('api.cities');
            Route::get('districts', [AdminController::class, 'getDistricts'])->name('api.districts');
            Route::get('subdistricts', [AdminController::class, 'getSubDistricts'])->name('api.subdistricts');
            Route::get('postalcodes', [AdminController::class, 'getPostalCodes'])->name('api.postalcodes');
        });
    });

    // =================== ADMIN & BENDAHARA ROUTES ===================
    // Middleware: checkrole:Admin,Bendahara
    Route::middleware(['checkrole:Admin,Bendahara'])->group(function () {
        
        // Kategori Management - Write Operations
        Route::prefix('kategori')->group(function () {
            Route::get('create', [App\Http\Controllers\KategoriController::class, 'create'])->name('kategori.create');
            Route::post('/', [App\Http\Controllers\KategoriController::class, 'store'])->name('kategori.store');
            Route::get('{id}/edit', [App\Http\Controllers\KategoriController::class, 'edit'])->name('kategori.edit');
            Route::put('{id}', [App\Http\Controllers\KategoriController::class, 'update'])->name('kategori.update');
            Route::delete('{id}', [App\Http\Controllers\KategoriController::class, 'destroy'])->name('kategori.destroy');
            
            // KBBI API endpoint for AJAX
            Route::get('api/kbbi', [App\Http\Controllers\KategoriController::class, 'searchKBBI'])->name('kategori.api.kbbi');
        });
        
        // Transaksi Management - Write Operations
        Route::prefix('transaksi')->group(function () {
            Route::get('create', [App\Http\Controllers\TransaksiController::class, 'create'])->name('transaksi.create');
            Route::post('/', [App\Http\Controllers\TransaksiController::class, 'store'])->name('transaksi.store');
            Route::get('{id}/edit', [App\Http\Controllers\TransaksiController::class, 'edit'])->name('transaksi.edit');
            Route::put('{id}', [App\Http\Controllers\TransaksiController::class, 'update'])->name('transaksi.update');
            Route::delete('{id}', [App\Http\Controllers\TransaksiController::class, 'destroy'])->name('transaksi.destroy');
            
            // Exchange Rate API endpoint for AJAX
            Route::get('api/exchange-rate', [App\Http\Controllers\TransaksiController::class, 'getExchangeRate'])->name('transaksi.api.exchange');
        });
        
        // Saldo Management - Write Operations (Orang 4)
        Route::prefix('saldo')->group(function () {
            Route::get('create', [App\Http\Controllers\SaldoController::class, 'create'])->name('saldo.create');
            Route::post('/', [App\Http\Controllers\SaldoController::class, 'store'])->name('saldo.store');
            Route::get('{id}/edit', [App\Http\Controllers\SaldoController::class, 'edit'])->name('saldo.edit');
            Route::put('{id}', [App\Http\Controllers\SaldoController::class, 'update'])->name('saldo.update');
            Route::delete('{id}', [App\Http\Controllers\SaldoController::class, 'destroy'])->name('saldo.destroy');
            
            // Excel Export
            Route::get('export-excel', [App\Http\Controllers\SaldoController::class, 'exportExcel'])->name('saldo.export.excel');
            
            // Chart.js API endpoint
            Route::get('api/chart', [App\Http\Controllers\SaldoController::class, 'getChartData'])->name('saldo.api.chart');
            
            // Recalculate saldo (sync with actual transactions)
            Route::post('recalculate', [App\Http\Controllers\SaldoController::class, 'recalculate'])->name('saldo.recalculate');
        });
        
        // Laporan Management - Write Operations (Orang 5)
        Route::prefix('laporan')->group(function () {
            Route::get('create', [App\Http\Controllers\LaporanController::class, 'create'])->name('laporan.create');
            Route::post('/', [App\Http\Controllers\LaporanController::class, 'store'])->name('laporan.store');
            Route::get('{id}/edit', [App\Http\Controllers\LaporanController::class, 'edit'])->name('laporan.edit');
            Route::put('{id}', [App\Http\Controllers\LaporanController::class, 'update'])->name('laporan.update');
            Route::delete('{id}', [App\Http\Controllers\LaporanController::class, 'destroy'])->name('laporan.destroy');
        });
    });

    // ===========================================
    // All Authenticated Users Routes (Read-Only)
    // ===========================================
    // Kategori Read-Only
    Route::get('kategori', [App\Http\Controllers\KategoriController::class, 'index'])->name('kategori.index');
    Route::get('kategori/{id}', [App\Http\Controllers\KategoriController::class, 'show'])->name('kategori.show');
    
    // Transaksi Read-Only
    Route::get('transaksi', [App\Http\Controllers\TransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('transaksi/{id}', [App\Http\Controllers\TransaksiController::class, 'show'])->name('transaksi.show');
    
    // Saldo Read-Only
    Route::get('saldo', [App\Http\Controllers\SaldoController::class, 'index'])->name('saldo.index');
    Route::get('saldo/{id}', [App\Http\Controllers\SaldoController::class, 'show'])->name('saldo.show');
    
    // Laporan Read-Only & Exports (All authenticated users)
    Route::get('laporan', [App\Http\Controllers\LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/{id}', [App\Http\Controllers\LaporanController::class, 'show'])->name('laporan.show');
    Route::get('laporan/{id}/pdf', [App\Http\Controllers\LaporanController::class, 'exportPDF'])->name('laporan.pdf');
    
    // Saldo Export - All authenticated users
    Route::get('saldo/export', [App\Http\Controllers\SaldoController::class, 'exportExcel'])->name('saldo.export');
    
    // Profile routes - All authenticated users
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
});

// =================== FALLBACK ROUTE ===================
Route::fallback(function () {
    return redirect('/dashboard')->with('error', 'Halaman tidak ditemukan.');
});
