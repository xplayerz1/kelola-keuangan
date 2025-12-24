<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    /**
     * Display a listing of transactions.
     * 
     * Accessible by: All authenticated users
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'category'])
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
        
        $transactions = $query->paginate(20);
        
        // Statistics - Get from active master_saldo (same as Saldo page)
        $activeSaldo = \App\Models\MasterSaldo::where('status', 'aktif')->first();
        
        if ($activeSaldo) {
            // Use values from master_saldo (auto-updated by Observer)
            $totalPemasukan = $activeSaldo->total_masuk;
            $totalPengeluaran = $activeSaldo->total_keluar;
            $saldo = $activeSaldo->saldo_akhir; // Includes saldo_awal!
        } else {
            // Fallback: calculate directly from transactions if no active period
            $totalPemasukan = Transaction::whereHas('category', function($q) {
                $q->where('jenis', 'Pemasukan');
            })->sum('nominal');
            
            $totalPengeluaran = Transaction::whereHas('category', function($q) {
                $q->where('jenis', 'Pengeluaran');
            })->sum('nominal');
            
            $saldo = $totalPemasukan - $totalPengeluaran; // No saldo_awal
        }
        
        $categories = Category::orderBy('nama_kategori')->get();
        
        return view('transaksi.index', compact('transactions', 'totalPemasukan', 'totalPengeluaran', 'saldo', 'categories'));
    }

    /**
     * Show the form for creating a new transaction.
     * 
     * Accessible by: Admin & Bendahara only
     */
    public function create()
    {
        $categories = Category::orderBy('jenis')->orderBy('nama_kategori')->get();
        
        return view('transaksi.create', compact('categories'));
    }

    /**
     * Store a newly created transaction in storage.
     * 
     * Accessible by: Admin & Bendahara only
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
            'category_id' => 'required|exists:categories,id',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'tanggal.required' => 'Tanggal wajib diisi',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh di masa depan',
            'category_id.required' => 'Kategori wajib dipilih',
            'category_id.exists' => 'Kategori tidak valid',
            'nominal.required' => 'Nominal wajib diisi',
            'nominal.min' => 'Nominal tidak boleh negatif',
        ]);

        // Add authenticated user ID
        $validated['user_id'] = auth()->id();

        Transaction::create($validated);
        
        $category = Category::find($validated['category_id']);

        return redirect()->route('transaksi.index')
            ->with('success', "Transaksi {$category->jenis} sebesar Rp " . number_format($validated['nominal'], 0, ',', '.') . " berhasil ditambahkan!");
    }

    /**
     * Display the specified transaction.
     * 
     * Accessible by: All authenticated users
     */
    public function show(string $id)
    {
        $transaction = Transaction::with(['user', 'category'])->findOrFail($id);
        
        return view('transaksi.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified transaction.
     * 
     * Accessible by: Admin & Bendahara only
     */
    public function edit(string $id)
    {
        $transaction = Transaction::findOrFail($id);
        $categories = Category::orderBy('jenis')->orderBy('nama_kategori')->get();
        
        return view('transaksi.edit', compact('transaction', 'categories'));
    }

    /**
     * Update the specified transaction in storage.
     * 
     * Accessible by: Admin & Bendahara only
     */
    public function update(Request $request, string $id)
    {
        $transaction = Transaction::findOrFail($id);
        
        $validated = $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
            'category_id' => 'required|exists:categories,id',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'tanggal.required' => 'Tanggal wajib diisi',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh di masa depan',
            'category_id.required' => 'Kategori wajib dipilih',
            'category_id.exists' => 'Kategori tidak valid',
            'nominal.required' => 'Nominal wajib diisi',
            'nominal.min' => 'Nominal tidak boleh negatif',
        ]);

        $transaction->update($validated);
        
        $category = Category::find($validated['category_id']);

        return redirect()->route('transaksi.index')
            ->with('success', "Transaksi {$category->jenis} berhasil diperbarui!");
    }

    /**
     * Remove the specified transaction from storage.
     * 
     * Accessible by: Admin & Bendahara only
     */
    public function destroy(string $id)
    {
        $transaction = Transaction::findOrFail($id);
        
        $nominal = $transaction->nominal;
        $jenis = $transaction->category->jenis;
        
        $transaction->delete();

        return redirect()->route('transaksi.index')
            ->with('success', "Transaksi {$jenis} sebesar Rp " . number_format($nominal, 0, ',', '.') . " berhasil dihapus!");
    }

    /**
     * Get Exchange Rate from USD to IDR
     * 
     * API: https://api.exchangerate-api.com/v4/latest/USD
     */
    public function getExchangeRate(Request $request)
    {
        try {
            $response = Http::timeout(30)
                ->withoutVerifying()
                ->get('https://api.exchangerate-api.com/v4/latest/USD');
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['rates']['IDR'])) {
                    return response()->json([
                        'status' => 200,
                        'message' => 'Berhasil',
                        'data' => [
                            'base' => 'USD',
                            'target' => 'IDR',
                            'rate' => $data['rates']['IDR'],
                            'date' => $data['date'] ?? date('Y-m-d'),
                            'formatted' => 'US$ 1 = Rp ' . number_format($data['rates']['IDR'], 0, ',', '.')
                        ]
                    ]);
                }
            }
            
            return response()->json([
                'status' => 500,
                'message' => 'Gagal mengambil data kurs',
                'data' => null
            ], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
