<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterSaldo;
use App\Models\HistoriSaldo;
use App\Models\Transaction;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HistoriSaldoExport;

class SaldoController extends Controller
{
    /**
     * Display saldo dashboard with Chart.js visualization
     * 
     * Accessible by: All authenticated users
     */
    public function index()
    {
        $activeSaldo = MasterSaldo::where('status', 'aktif')->first();
        
        // Get all saldo periods for chart
        $saldoPeriods = MasterSaldo::orderBy('created_at', 'asc')->get();
        
        // Get recent histori for active period
        $recentHistori = [];
        if ($activeSaldo) {
            $recentHistori = HistoriSaldo::with(['transaction.category', 'transaction.user'])
                ->where('id_saldo', $activeSaldo->id)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
        }
        
        // Statistics
        $totalTransaksi = Transaction::count();
        $persentasePenggunaan = $activeSaldo && $activeSaldo->saldo_awal > 0 
            ? round(($activeSaldo->total_keluar / $activeSaldo->saldo_awal) * 100, 2)
            : 0;
        
        return view('saldo.index', compact('activeSaldo', 'saldoPeriods', 'recentHistori', 'totalTransaksi', 'persentasePenggunaan'));
    }

    /**
     * Show the form for creating a new saldo period (Set Saldo Awal)
     * 
     * Accessible by: Admin & Bendahara only
     */
    public function create()
    {
        // Check if there's already an active period
        $activeSaldo = MasterSaldo::where('status', 'aktif')->first();
        
        return view('saldo.create', compact('activeSaldo'));
    }

    /**
     * Store a newly created saldo period
     * 
     * Accessible by: Admin & Bendahara only
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'periode' => 'required|string|max:50|unique:master_saldo,periode',
            'saldo_awal' => 'required|numeric|min:0',
        ], [
            'periode.required' => 'Periode wajib diisi',
            'periode.unique' => 'Periode sudah ada, tidak boleh tumpang tindih',
            'saldo_awal.required' => 'Saldo awal wajib diisi',
            'saldo_awal.min' => 'Saldo awal tidak boleh negatif',
        ]);

        // Close previous active period if exists
        MasterSaldo::where('status', 'aktif')->update(['status' => 'tutup']);

        // Create new period
        $saldo = MasterSaldo::create([
            'periode' => $validated['periode'],
            'saldo_awal' => $validated['saldo_awal'],
            'total_masuk' => 0,
            'total_keluar' => 0,
            'saldo_akhir' => $validated['saldo_awal'],
            'status' => 'aktif',
        ]);

        // Create initial histori
        HistoriSaldo::create([
            'id_saldo' => $saldo->id,
            'transaction_id' => null,
            'nominal' => $validated['saldo_awal'],
            'saldo_sebelum' => 0,
            'saldo_sesudah' => $validated['saldo_awal'],
            'keterangan' => 'Set saldo awal periode ' . $validated['periode'],
        ]);

        return redirect()->route('saldo.index')
            ->with('success', "Periode '{$validated['periode']}' berhasil dibuat dengan saldo awal Rp " . number_format($validated['saldo_awal'], 0, ',', '.'));
    }

    /**
     * Show the form for editing the specified saldo period
     * 
     * Accessible by: Admin & Bendahara only
     */
    public function edit(string $id)
    {
        $saldo = MasterSaldo::findOrFail($id);
        
        // Count how many histori records (can't edit if has transactions)
        $historiCount = HistoriSaldo::where('id_saldo', $id)
            ->whereNotNull('transaction_id')
            ->count();
        
        return view('saldo.edit', compact('saldo', 'historiCount'));
    }

    /**
     * Update the specified saldo period in storage
     * 
     * Accessible by: Admin & Bendahara only
     */
    public function update(Request $request, string $id)
    {
        $saldo = MasterSaldo::findOrFail($id);
        
        $validated = $request->validate([
            'periode' => 'required|string|max:50|unique:master_saldo,periode,' . $id,
            'saldo_awal' => 'required|numeric|min:0',
        ], [
            'periode.required' => 'Periode wajib diisi',
            'periode.unique' => 'Periode sudah ada, tidak boleh duplikat',
            'saldo_awal.required' => 'Saldo awal wajib diisi',
            'saldo_awal.min' => 'Saldo awal tidak boleh negatif',
        ]);

        $oldSaldoAwal = $saldo->saldo_awal;
        $difference = $validated['saldo_awal'] - $oldSaldoAwal;
        
        // Update saldo awal and recalculate saldo akhir
        $saldo->periode = $validated['periode'];
        $saldo->saldo_awal = $validated['saldo_awal'];
        $saldo->saldo_akhir = $saldo->saldo_akhir + $difference;
        $saldo->save();
        
        // Create histori record for this edit
        if ($difference != 0) {
            HistoriSaldo::create([
                'id_saldo' => $saldo->id,
                'transaction_id' => null,
                'nominal' => abs($difference),
                'saldo_sebelum' => $oldSaldoAwal,
                'saldo_sesudah' => $validated['saldo_awal'],
                'keterangan' => 'Edit saldo awal periode ' . $validated['periode'] . ' (' . ($difference > 0 ? '+' : '') . number_format($difference, 0, ',', '.') . ')',
            ]);
        }

        return redirect()->route('saldo.index')
            ->with('success', "Periode '{$validated['periode']}' berhasil diperbarui!");
    }

    /**
     * Display the specified saldo period
     */
    public function show(string $id)
    {
        $saldo = MasterSaldo::with(['historiSaldo' => function($q) {
            $q->with(['transaction.category', 'transaction.user'])->orderBy('created_at', 'desc');
        }])->findOrFail($id);
        
        return view('saldo.show', compact('saldo'));
    }

    /**
     * Close or Permanently Delete a saldo period
     * 
     * Accessible by: Admin & Bendahara only
     * - If 'permanent' query param: Delete permanently (only if no transactions)
     * - Otherwise: Close period (change status to 'tutup')
     */
    public function destroy(Request $request, string $id)
    {
        $saldo = MasterSaldo::findOrFail($id);
        
        // Check if permanent delete is requested
        if ($request->query('permanent') === 'true') {
            // Count transactions in this period
            $transactionCount = HistoriSaldo::where('id_saldo', $id)
                ->whereNotNull('transaction_id')
                ->count();
            
            if ($transactionCount > 0) {
                return back()->with('error', "Periode '{$saldo->periode}' tidak dapat dihapus karena masih memiliki {$transactionCount} transaksi.");
            }
            
            // Safe to delete - no transactions
            $periode = $saldo->periode;
            $saldo->delete(); // This will cascade delete histori_saldo
            
            return redirect()->route('saldo.index')
                ->with('success', "Periode '{$periode}' berhasil dihapus permanent!");
        }
        
        // Just close the period
        if ($saldo->status === 'aktif') {
            $saldo->update(['status' => 'tutup']);
            
            return redirect()->route('saldo.index')
                ->with('success', "Periode '{$saldo->periode}' berhasil ditutup.");
        }
        
        return back()->with('error', 'Hanya periode aktif yang dapat ditutup.');
    }

    /**
     * Export histori saldo to Excel
     */
    public function exportExcel(Request $request)
    {
        $saldoId = $request->get('saldo_id');
        
        if ($saldoId) {
            $saldo = MasterSaldo::findOrFail($saldoId);
            $filename = 'Histori_Saldo_' . $saldo->periode . '_' . date('Y-m-d') . '.xlsx';
        } else {
            $filename = 'Histori_Saldo_All_' . date('Y-m-d') . '.xlsx';
        }
        
        return Excel::download(new HistoriSaldoExport($saldoId), $filename);
    }

    /**
     * Get saldo data for Chart.js (API endpoint)
     * Limited to last 12 periods to prevent lag
     */
    public function getChartData()
    {
        // Get only last 12 periods to prevent chart lag
        $periods = MasterSaldo::orderBy('created_at', 'desc')
            ->limit(12)
            ->get()
            ->reverse(); // Reverse to show chronological order
        
        $labels = [];
        $saldoData = [];
        $pemasukanData = [];
        $pengeluaranData = [];
        
        foreach ($periods as $period) {
            $labels[] = $period->periode;
            $saldoData[] = $period->saldo_akhir;
            $pemasukanData[] = $period->total_masuk;
            $pengeluaranData[] = $period->total_keluar;
        }
        
        return response()->json([
            'status' => 200,
            'data' => [
                'labels' => $labels,
                'saldo' => $saldoData,
            'pemasukan' => $pemasukanData,
                'pengeluaran' => $pengeluaranData,
            ]
        ]);
    }
    
    /**
     * Recalculate master_saldo based on actual transaction data
     * Use this to fix data discrepancies
     * 
     * Accessible by: Admin only
     */
    public function recalculate()
    {
        $activeSaldo = MasterSaldo::where('status', 'aktif')->first();
        
        if (!$activeSaldo) {
            return back()->with('warning', 'Tidak ada periode saldo aktif.');
        }
        
        // Calculate actual totals from transactions
        $actualPemasukan = Transaction::whereHas('category', function($q) {
            $q->where('jenis', 'Pemasukan');
        })->sum('nominal');
        
        $actualPengeluaran = Transaction::whereHas('category', function($q) {
            $q->where('jenis', 'Pengeluaran');
        })->sum('nominal');
        
        // Store old values for logging
        $oldTotalMasuk = $activeSaldo->total_masuk;
        $oldTotalKeluar = $activeSaldo->total_keluar;
        $oldSaldoAkhir = $activeSaldo->saldo_akhir;
        
        // Update with actual values
        $activeSaldo->total_masuk = $actualPemasukan;
        $activeSaldo->total_keluar = $actualPengeluaran;
        $activeSaldo->saldo_akhir = $activeSaldo->saldo_awal + $actualPemasukan - $actualPengeluaran;
        $activeSaldo->save();
        
        // Log the recalculation
        HistoriSaldo::create([
            'id_saldo' => $activeSaldo->id,
            'transaction_id' => null,
            'nominal' => 0,
            'saldo_sebelum' => $oldSaldoAkhir,
            'saldo_sesudah' => $activeSaldo->saldo_akhir,
            'keterangan' => 'Recalculate: Masuk ' . number_format($oldTotalMasuk) . '→' . number_format($actualPemasukan) . 
                           ', Keluar ' . number_format($oldTotalKeluar) . '→' . number_format($actualPengeluaran),
        ]);
        
        return back()->with('success', 'Data saldo berhasil di-sync! ' .
            'Pemasukan: Rp ' . number_format($actualPemasukan, 0, ',', '.') . ', ' .
            'Pengeluaran: Rp ' . number_format($actualPengeluaran, 0, ',', '.') . ', ' .
            'Saldo Akhir: Rp ' . number_format($activeSaldo->saldo_akhir, 0, ',', '.'));
    }
}
