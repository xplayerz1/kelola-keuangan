<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * REST API Controller untuk Kategori
 * 
 * Menyediakan endpoint GET untuk mengakses data kategori
 * melalui REST API.
 */
class KategoriApiController extends Controller
{
    /**
     * Get all categories with statistics
     * 
     * GET /api/kategori
     * 
     * Query Parameters:
     * - jenis: Filter by jenis (Pemasukan/Pengeluaran)
     * - search: Search by nama_kategori
     * - per_page: Number of items per page (default: all)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Category::withCount('transactions')
                ->orderBy('jenis', 'asc')
                ->orderBy('nama_kategori', 'asc');
            
            // Filter by jenis
            if ($request->filled('jenis')) {
                $query->where('jenis', $request->jenis);
            }
            
            // Search by nama_kategori
            if ($request->filled('search')) {
                $query->where('nama_kategori', 'like', '%' . $request->search . '%');
            }
            
            // Pagination or all
            if ($request->filled('per_page')) {
                $categories = $query->paginate($request->per_page);
            } else {
                $categories = $query->get();
            }
            
            // Statistics
            $stats = [
                'total_kategori' => Category::count(),
                'total_pemasukan' => Category::where('jenis', 'Pemasukan')->count(),
                'total_pengeluaran' => Category::where('jenis', 'Pengeluaran')->count(),
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Data kategori berhasil diambil',
                'data' => $categories,
                'statistics' => $stats,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kategori: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Get single category by ID with related transactions
     * 
     * GET /api/kategori/{id}
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $category = Category::with(['transactions' => function($query) {
                $query->orderBy('tanggal', 'desc')->take(10);
            }])->withCount('transactions')->findOrFail($id);
            
            // Additional statistics for this category
            $stats = [
                'total_transaksi' => $category->transactions()->count(),
                'total_nominal' => $category->transactions()->sum('nominal'),
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Detail kategori berhasil diambil',
                'data' => $category,
                'statistics' => $stats,
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail kategori: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
