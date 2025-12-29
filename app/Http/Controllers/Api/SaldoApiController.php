<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterSaldo;
use App\Models\HistoriSaldo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * REST API Controller untuk Saldo
 * 
 * Menyediakan endpoint GET untuk mengakses data saldo/periode
 * melalui REST API.
 */
class SaldoApiController extends Controller
{
    /**
     * Get all saldo periods with statistics
     * 
     * GET /api/saldo
     * 
     * Query Parameters:
     * - status: Filter by status (aktif/tutup)
     * - per_page: Number of items per page (default: all)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = MasterSaldo::orderBy('created_at', 'desc');
            
            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            // Get active period
            $activeSaldo = MasterSaldo::where('status', 'aktif')->first();
            
            // Pagination or all
            if ($request->filled('per_page')) {
                $saldos = $query->paginate($request->per_page);
            } else {
                $saldos = $query->get();
            }
            
            // Format saldo data
            $formattedSaldos = collect($saldos instanceof \Illuminate\Pagination\LengthAwarePaginator ? $saldos->items() : $saldos)->map(function($saldo) {
                return [
                    'id' => $saldo->id,
                    'periode' => $saldo->periode,
                    'saldo_awal' => (float) $saldo->saldo_awal,
                    'total_masuk' => (float) $saldo->total_masuk,
                    'total_keluar' => (float) $saldo->total_keluar,
                    'saldo_akhir' => (float) $saldo->saldo_akhir,
                    'status' => $saldo->status,
                    'created_at' => $saldo->created_at,
                    'updated_at' => $saldo->updated_at,
                    'formatted' => [
                        'saldo_awal' => 'Rp ' . number_format($saldo->saldo_awal, 0, ',', '.'),
                        'total_masuk' => 'Rp ' . number_format($saldo->total_masuk, 0, ',', '.'),
                        'total_keluar' => 'Rp ' . number_format($saldo->total_keluar, 0, ',', '.'),
                        'saldo_akhir' => 'Rp ' . number_format($saldo->saldo_akhir, 0, ',', '.'),
                    ]
                ];
            });
            
            // Statistics
            $stats = [
                'total_periode' => MasterSaldo::count(),
                'periode_aktif' => MasterSaldo::where('status', 'aktif')->count(),
                'periode_tutup' => MasterSaldo::where('status', 'tutup')->count(),
                'active_saldo' => $activeSaldo ? [
                    'id' => $activeSaldo->id,
                    'periode' => $activeSaldo->periode,
                    'saldo_akhir' => (float) $activeSaldo->saldo_akhir,
                    'formatted_saldo_akhir' => 'Rp ' . number_format($activeSaldo->saldo_akhir, 0, ',', '.'),
                ] : null,
            ];
            
            // Get history for all periods
            $history = HistoriSaldo::with('masterSaldo:id,periode')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Data saldo berhasil diambil',
                'data' => $formattedSaldos,
                'statistics' => $stats,
                'recent_history' => $history,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data saldo: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Get single saldo period by ID with history
     * 
     * GET /api/saldo/{id}
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $saldo = MasterSaldo::with(['historiSaldo' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])->findOrFail($id);
            
            $saldoData = [
                'id' => $saldo->id,
                'periode' => $saldo->periode,
                'saldo_awal' => (float) $saldo->saldo_awal,
                'total_masuk' => (float) $saldo->total_masuk,
                'total_keluar' => (float) $saldo->total_keluar,
                'saldo_akhir' => (float) $saldo->saldo_akhir,
                'status' => $saldo->status,
                'created_at' => $saldo->created_at,
                'updated_at' => $saldo->updated_at,
                'formatted' => [
                    'saldo_awal' => 'Rp ' . number_format($saldo->saldo_awal, 0, ',', '.'),
                    'total_masuk' => 'Rp ' . number_format($saldo->total_masuk, 0, ',', '.'),
                    'total_keluar' => 'Rp ' . number_format($saldo->total_keluar, 0, ',', '.'),
                    'saldo_akhir' => 'Rp ' . number_format($saldo->saldo_akhir, 0, ',', '.'),
                ],
                'histori_saldo' => $saldo->historiSaldo,
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Detail saldo berhasil diambil',
                'data' => $saldoData,
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Periode saldo tidak ditemukan',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail saldo: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Get chart data for saldo visualization
     * 
     * GET /api/saldo/chart
     * 
     * @return JsonResponse
     */
    public function chart(): JsonResponse
    {
        try {
            $saldos = MasterSaldo::orderBy('created_at', 'asc')
                ->take(12)
                ->get(['id', 'periode', 'saldo_awal', 'total_masuk', 'total_keluar', 'saldo_akhir']);
            
            $chartData = [
                'labels' => $saldos->pluck('periode'),
                'datasets' => [
                    [
                        'label' => 'Pemasukan',
                        'data' => $saldos->pluck('total_masuk'),
                        'backgroundColor' => 'rgba(40, 167, 69, 0.5)',
                        'borderColor' => 'rgb(40, 167, 69)',
                    ],
                    [
                        'label' => 'Pengeluaran',
                        'data' => $saldos->pluck('total_keluar'),
                        'backgroundColor' => 'rgba(220, 53, 69, 0.5)',
                        'borderColor' => 'rgb(220, 53, 69)',
                    ],
                    [
                        'label' => 'Saldo Akhir',
                        'data' => $saldos->pluck('saldo_akhir'),
                        'backgroundColor' => 'rgba(0, 123, 255, 0.5)',
                        'borderColor' => 'rgb(0, 123, 255)',
                    ],
                ]
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Data chart saldo berhasil diambil',
                'data' => $chartData,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data chart: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
