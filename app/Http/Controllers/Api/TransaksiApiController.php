<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\MasterSaldo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * REST API Controller untuk Transaksi
 * 
 * Menyediakan endpoint GET untuk mengakses data transaksi
 * melalui REST API.
 */
class TransaksiApiController extends Controller
{
    /**
     * Get all transactions with filters and statistics
     * 
     * GET /api/transaksi
     * 
     * Query Parameters:
     * - start_date: Filter transaksi dari tanggal ini
     * - end_date: Filter transaksi sampai tanggal ini
     * - category_id: Filter by category ID
     * - jenis: Filter by jenis (Pemasukan/Pengeluaran)
     * - per_page: Number of items per page (default: 20)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Transaction::with(['user:id,name', 'category:id,nama_kategori,jenis'])
                ->orderBy('tanggal', 'desc')
                ->orderBy('created_at', 'desc');
            
            // Filter by date range
            if ($request->filled('start_date')) {
                $query->whereDate('tanggal', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('tanggal', '<=', $request->end_date);
            }
            
            // Filter by category
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            
            // Filter by jenis (via category)
            if ($request->filled('jenis')) {
                $query->whereHas('category', function($q) use ($request) {
                    $q->where('jenis', $request->jenis);
                });
            }
            
            // Pagination
            $perPage = $request->get('per_page', 20);
            $transactions = $query->paginate($perPage);
            
            // Statistics - Get from active master_saldo
            $activeSaldo = MasterSaldo::where('status', 'aktif')->first();
            
            if ($activeSaldo) {
                $totalPemasukan = $activeSaldo->total_masuk;
                $totalPengeluaran = $activeSaldo->total_keluar;
                $saldo = $activeSaldo->saldo_akhir;
            } else {
                // Fallback: calculate directly from transactions
                $totalPemasukan = Transaction::whereHas('category', function($q) {
                    $q->where('jenis', 'Pemasukan');
                })->sum('nominal');
                
                $totalPengeluaran = Transaction::whereHas('category', function($q) {
                    $q->where('jenis', 'Pengeluaran');
                })->sum('nominal');
                
                $saldo = $totalPemasukan - $totalPengeluaran;
            }
            
            $stats = [
                'total_transaksi' => Transaction::count(),
                'total_pemasukan' => (float) $totalPemasukan,
                'total_pengeluaran' => (float) $totalPengeluaran,
                'saldo' => (float) $saldo,
                'formatted' => [
                    'total_pemasukan' => 'Rp ' . number_format($totalPemasukan, 0, ',', '.'),
                    'total_pengeluaran' => 'Rp ' . number_format($totalPengeluaran, 0, ',', '.'),
                    'saldo' => 'Rp ' . number_format($saldo, 0, ',', '.'),
                ]
            ];
            
            // Get categories for filter dropdown
            $categories = Category::orderBy('nama_kategori')->get(['id', 'nama_kategori', 'jenis']);
            
            return response()->json([
                'success' => true,
                'message' => 'Data transaksi berhasil diambil',
                'data' => $transactions,
                'statistics' => $stats,
                'categories' => $categories,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data transaksi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Get single transaction by ID
     * 
     * GET /api/transaksi/{id}
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $transaction = Transaction::with(['user:id,name,email', 'category'])->findOrFail($id);
            
            // Format data for display
            $transactionData = $transaction->toArray();
            $transactionData['formatted_nominal'] = 'Rp ' . number_format($transaction->nominal, 0, ',', '.');
            $transactionData['formatted_tanggal'] = $transaction->tanggal->format('d F Y');
            
            return response()->json([
                'success' => true,
                'message' => 'Detail transaksi berhasil diambil',
                'data' => $transactionData,
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail transaksi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
