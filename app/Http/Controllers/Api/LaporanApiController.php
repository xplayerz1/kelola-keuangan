<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * REST API Controller untuk Laporan
 * 
 * Menyediakan endpoint GET untuk mengakses data laporan
 * melalui REST API.
 */
class LaporanApiController extends Controller
{
    /**
     * Get all reports with statistics
     * 
     * GET /api/laporan
     * 
     * Query Parameters:
     * - status: Filter by status (draft/published)
     * - year: Filter by year
     * - per_page: Number of items per page (default: 20)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Laporan::with('user:id,name')
                ->orderBy('created_at', 'desc');
            
            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            // Filter by year
            if ($request->filled('year')) {
                $query->whereYear('start_date', $request->year);
            }
            
            // Pagination
            $perPage = $request->get('per_page', 20);
            $laporans = $query->paginate($perPage);
            
            // Format laporan data
            $formattedLaporans = collect($laporans->items())->map(function($laporan) {
                return [
                    'id' => $laporan->id,
                    'judul' => $laporan->judul,
                    'start_date' => $laporan->start_date->format('Y-m-d'),
                    'end_date' => $laporan->end_date->format('Y-m-d'),
                    'total_pemasukan' => (float) $laporan->total_pemasukan,
                    'total_pengeluaran' => (float) $laporan->total_pengeluaran,
                    'selisih' => (float) $laporan->selisih,
                    'status' => $laporan->status,
                    'catatan' => $laporan->catatan,
                    'generated_by' => $laporan->user ? $laporan->user->name : null,
                    'created_at' => $laporan->created_at,
                    'formatted' => [
                        'periode' => $laporan->start_date->format('d M Y') . ' - ' . $laporan->end_date->format('d M Y'),
                        'total_pemasukan' => 'Rp ' . number_format($laporan->total_pemasukan, 0, ',', '.'),
                        'total_pengeluaran' => 'Rp ' . number_format($laporan->total_pengeluaran, 0, ',', '.'),
                        'selisih' => 'Rp ' . number_format(abs($laporan->selisih), 0, ',', '.'),
                        'selisih_label' => $laporan->selisih >= 0 ? 'Surplus' : 'Defisit',
                    ]
                ];
            });
            
            // Statistics
            $stats = [
                'total_laporan' => Laporan::count(),
                'laporan_published' => Laporan::where('status', 'published')->count(),
                'laporan_draft' => Laporan::where('status', 'draft')->count(),
                'total_surplus' => (float) Laporan::where('selisih', '>=', 0)->sum('selisih'),
                'total_defisit' => (float) abs(Laporan::where('selisih', '<', 0)->sum('selisih')),
            ];
            
            // Available years for filter
            $years = Laporan::selectRaw('YEAR(start_date) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year');
            
            return response()->json([
                'success' => true,
                'message' => 'Data laporan berhasil diambil',
                'data' => $formattedLaporans,
                'pagination' => [
                    'current_page' => $laporans->currentPage(),
                    'last_page' => $laporans->lastPage(),
                    'per_page' => $laporans->perPage(),
                    'total' => $laporans->total(),
                ],
                'statistics' => $stats,
                'available_years' => $years,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data laporan: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Get single report by ID with transaction breakdown
     * 
     * GET /api/laporan/{id}
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $laporan = Laporan::with('user:id,name,email')->findOrFail($id);
            
            // Get transactions within report date range
            $transactions = Transaction::with(['category:id,nama_kategori,jenis', 'user:id,name'])
                ->whereBetween('tanggal', [$laporan->start_date, $laporan->end_date])
                ->orderBy('tanggal', 'desc')
                ->get();
            
            // Group by category for breakdown
            $categoryBreakdown = $transactions->groupBy('category.jenis')->map(function($group) {
                return $group->groupBy('category.nama_kategori')->map(function($catTransactions) {
                    return [
                        'count' => $catTransactions->count(),
                        'total' => (float) $catTransactions->sum('nominal'),
                        'formatted_total' => 'Rp ' . number_format($catTransactions->sum('nominal'), 0, ',', '.'),
                    ];
                });
            });
            
            $laporanData = [
                'id' => $laporan->id,
                'judul' => $laporan->judul,
                'start_date' => $laporan->start_date->format('Y-m-d'),
                'end_date' => $laporan->end_date->format('Y-m-d'),
                'total_pemasukan' => (float) $laporan->total_pemasukan,
                'total_pengeluaran' => (float) $laporan->total_pengeluaran,
                'selisih' => (float) $laporan->selisih,
                'status' => $laporan->status,
                'catatan' => $laporan->catatan,
                'keterangan_libur' => $laporan->keterangan_libur,
                'dashboard_screenshot' => $laporan->dashboard_screenshot,
                'generated_by' => $laporan->user ? [
                    'id' => $laporan->user->id,
                    'name' => $laporan->user->name,
                    'email' => $laporan->user->email,
                ] : null,
                'created_at' => $laporan->created_at,
                'updated_at' => $laporan->updated_at,
                'formatted' => [
                    'periode' => $laporan->start_date->format('d F Y') . ' - ' . $laporan->end_date->format('d F Y'),
                    'total_pemasukan' => 'Rp ' . number_format($laporan->total_pemasukan, 0, ',', '.'),
                    'total_pengeluaran' => 'Rp ' . number_format($laporan->total_pengeluaran, 0, ',', '.'),
                    'selisih' => 'Rp ' . number_format(abs($laporan->selisih), 0, ',', '.'),
                    'selisih_label' => $laporan->selisih >= 0 ? 'Surplus' : 'Defisit',
                ],
                'transactions' => $transactions->map(function($trx) {
                    return [
                        'id' => $trx->id,
                        'tanggal' => $trx->tanggal->format('Y-m-d'),
                        'nominal' => (float) $trx->nominal,
                        'keterangan' => $trx->keterangan,
                        'category' => $trx->category ? [
                            'nama' => $trx->category->nama_kategori,
                            'jenis' => $trx->category->jenis,
                        ] : null,
                        'user' => $trx->user ? $trx->user->name : null,
                        'formatted_nominal' => 'Rp ' . number_format($trx->nominal, 0, ',', '.'),
                        'formatted_tanggal' => $trx->tanggal->format('d M Y'),
                    ];
                }),
                'category_breakdown' => $categoryBreakdown,
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Detail laporan berhasil diambil',
                'data' => $laporanData,
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail laporan: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
