<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\KategoriApiController;
use App\Http\Controllers\Api\TransaksiApiController;
use App\Http\Controllers\Api\SaldoApiController;
use App\Http\Controllers\Api\LaporanApiController;
use App\Http\Controllers\Api\UserApiController;

/*
|--------------------------------------------------------------------------
| REST API Routes - HMSI Finance Management System
|--------------------------------------------------------------------------
|
| Semua route di sini menggunakan prefix /api/ dan sudah dilindungi
| oleh middleware 'web' untuk session-based authentication.
|
| Endpoint yang tersedia (GET only):
| - GET /api/kategori          - Daftar kategori
| - GET /api/kategori/{id}     - Detail kategori
| - GET /api/transaksi         - Daftar transaksi
| - GET /api/transaksi/{id}    - Detail transaksi
| - GET /api/saldo             - Daftar periode saldo
| - GET /api/saldo/{id}        - Detail periode saldo
| - GET /api/saldo/chart       - Data chart saldo
| - GET /api/laporan           - Daftar laporan
| - GET /api/laporan/{id}      - Detail laporan
| - GET /api/users             - Daftar user (Admin only)
| - GET /api/users/{id}        - Detail user (Admin only)
|
*/

// =================== PUBLIC API INFO ===================
Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'HMSI Finance REST API v1.0',
        'documentation' => [
            'version' => '1.0.0',
            'base_url' => url('/api'),
            'authentication' => 'Session-based (Cookie)',
            'endpoints' => [
                'kategori' => [
                    'GET /api/kategori' => 'List all categories',
                    'GET /api/kategori/{id}' => 'Get category details',
                ],
                'transaksi' => [
                    'GET /api/transaksi' => 'List all transactions',
                    'GET /api/transaksi/{id}' => 'Get transaction details',
                ],
                'saldo' => [
                    'GET /api/saldo' => 'List all saldo periods',
                    'GET /api/saldo/{id}' => 'Get saldo period details',
                    'GET /api/saldo/chart' => 'Get chart data for visualization',
                ],
                'laporan' => [
                    'GET /api/laporan' => 'List all reports',
                    'GET /api/laporan/{id}' => 'Get report details with transactions',
                ],
                'users' => [
                    'GET /api/users' => 'List all users (Admin only)',
                    'GET /api/users/{id}' => 'Get user details (Admin only)',
                ],
            ],
        ],
    ]);
});

// =================== AUTHENTICATED API ROUTES ===================
// Using web middleware for session-based authentication
Route::middleware(['web', 'auth'])->group(function () {
    
    // ===== Kategori API =====
    // Accessible by: All authenticated users
    Route::prefix('kategori')->group(function () {
        Route::get('/', [KategoriApiController::class, 'index'])->name('api.kategori.index');
        Route::get('/{id}', [KategoriApiController::class, 'show'])->name('api.kategori.show');
    });
    
    // ===== Transaksi API =====
    // Accessible by: All authenticated users
    Route::prefix('transaksi')->group(function () {
        Route::get('/', [TransaksiApiController::class, 'index'])->name('api.transaksi.index');
        Route::get('/{id}', [TransaksiApiController::class, 'show'])->name('api.transaksi.show');
    });
    
    // ===== Saldo API =====
    // Accessible by: Admin & Bendahara
    Route::middleware(['checkrole:Admin,Bendahara'])->prefix('saldo')->group(function () {
        Route::get('/', [SaldoApiController::class, 'index'])->name('api.saldo.index');
        Route::get('/chart', [SaldoApiController::class, 'chart'])->name('api.saldo.chart');
        Route::get('/{id}', [SaldoApiController::class, 'show'])->name('api.saldo.show');
    });
    
    // ===== Laporan API =====
    // Accessible by: All authenticated users
    Route::prefix('laporan')->group(function () {
        Route::get('/', [LaporanApiController::class, 'index'])->name('api.laporan.index');
        Route::get('/{id}', [LaporanApiController::class, 'show'])->name('api.laporan.show');
    });
    
    // ===== Users API =====
    // Accessible by: Admin only
    Route::middleware(['checkrole:Admin'])->prefix('users')->group(function () {
        Route::get('/', [UserApiController::class, 'index'])->name('api.users.index');
        Route::get('/{id}', [UserApiController::class, 'show'])->name('api.users.show');
    });
});
