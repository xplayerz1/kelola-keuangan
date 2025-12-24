<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laporan;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    /**
     * Display a listing of reports
     * Accessible by: All authenticated users
     */
    public function index(Request $request)
    {
        $query = Laporan::with('user')->orderBy('created_at', 'desc');
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $laporan = $query->paginate(15);
        
        return view('laporan.index', compact('laporan'));
    }

    /**
     * Show the form for creating a new report
     * Accessible by: Admin & Bendahara only
     */
    public function create()
    {
        return view('laporan.create');
    }

    /**
     * Store a newly created report
     * Accessible by: Admin & Bendahara only
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:draft,published',
            'catatan' => 'nullable|string',
        ], [
            'judul.required' => 'Judul laporan wajib diisi',
            'start_date.required' => 'Tanggal mulai wajib diisi',
            'end_date.required' => 'Tanggal akhir wajib diisi',
            'end_date.after_or_equal' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal mulai',
        ]);

        // Get transactions in date range
        $transactions = Transaction::with(['category', 'user'])
            ->whereBetween('tanggal', [$validated['start_date'], $validated['end_date']])
            ->get();

        // Calculate totals
        $totalPemasukan = $transactions->filter(fn($t) => $t->category->jenis === 'Pemasukan')->sum('nominal');
        $totalPengeluaran = $transactions->filter(fn($t) => $t->category->jenis === 'Pengeluaran')->sum('nominal');
        $selisih = $totalPemasukan - $totalPengeluaran;

        // Get timezone & timestamp from World Time API
        $timeInfo = $this->getWorldTime();

        // Create laporan
        $laporan = Laporan::create([
            'judul' => $validated['judul'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_pemasukan' => $totalPemasukan,
            'total_pengeluaran' => $totalPengeluaran,
            'selisih' => $selisih,
            'status' => $validated['status'],
            'generated_by' => auth()->id(),
            'keterangan_libur' => $timeInfo, // World Time API data
            'catatan' => $validated['catatan'],
        ]);

        return redirect()->route('laporan.show', $laporan->id)
            ->with('success', "Laporan '{$validated['judul']}' berhasil dibuat!");
    }

    /**
     * Display the specified report with category aggregation
     * Accessible by: All authenticated users
     */
    public function show(string $id)
    {
        $laporan = Laporan::with('user')->findOrFail($id);

        // Get transactions for this report
        $transactions = Transaction::with(['category', 'user'])
            ->whereBetween('tanggal', [$laporan->start_date, $laporan->end_date])
            ->orderBy('tanggal', 'desc')
            ->get();

        // RELASI 2 TABEL: Aggregate by category (transactions + categories)
        $categoryBreakdown = Transaction::selectRaw('
                categories.id,
                categories.nama_kategori,
                categories.jenis,
                COUNT(transactions.id) as jumlah_transaksi,
                SUM(transactions.nominal) as total_nominal
            ')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->whereBetween('transactions.tanggal', [$laporan->start_date, $laporan->end_date])
            ->groupBy('categories.id', 'categories.nama_kategori', 'categories.jenis')
            ->orderBy('categories.jenis')
            ->orderBy('total_nominal', 'desc')
            ->get();

        return view('laporan.show', compact('laporan', 'transactions', 'categoryBreakdown'));
    }

    /**
     * Show the form for editing the specified report
     * Accessible by: Admin & Bendahara only
     */
    public function edit(string $id)
    {
        $laporan = Laporan::findOrFail($id);
        return view('laporan.edit', compact('laporan'));
    }

    /**
     * Update the specified report
     * Accessible by: Admin & Bendahara only
     */
    public function update(Request $request, string $id)
    {
        $laporan = Laporan::findOrFail($id);

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'status' => 'required|in:draft,published',
            'catatan' => 'nullable|string',
        ]);

        $laporan->update($validated);

        return redirect()->route('laporan.show', $laporan->id)
            ->with('success', "Laporan '{$validated['judul']}' berhasil diperbarui!");
    }

    /**
     * Remove the specified report
     * Accessible by: Admin & Bendahara only
     */
    public function destroy(string $id)
    {
        $laporan = Laporan::findOrFail($id);
        $judul = $laporan->judul;
        $laporan->delete();

        return redirect()->route('laporan.index')
            ->with('success', "Laporan '{$judul}' berhasil dihapus!");
    }

    /**
     * Export report to PDF
     * Accessible by: All authenticated users
     */
    public function exportPDF(string $id)
    {
        $laporan = Laporan::with('user')->findOrFail($id);
        
        $transactions = Transaction::with(['category', 'user'])
            ->whereBetween('tanggal', [$laporan->start_date, $laporan->end_date])
            ->orderBy('tanggal', 'desc')
            ->get();

        // Category breakdown
        $categoryBreakdown = Transaction::selectRaw('
                categories.nama_kategori,
                categories.jenis,
                COUNT(transactions.id) as jumlah_transaksi,
                SUM(transactions.nominal) as total_nominal
            ')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->whereBetween('transactions.tanggal', [$laporan->start_date, $laporan->end_date])
            ->groupBy('categories.nama_kategori', 'categories.jenis')
            ->orderBy('categories.jenis')
            ->get();

        $pdf = Pdf::loadView('laporan.pdf', compact('laporan', 'transactions', 'categoryBreakdown'));
        
        $filename = 'Laporan_' . str_replace(' ', '_', $laporan->judul) . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Get timezone & timestamp from World Time API
     * For audit trail timestamp with accurate timezone
     * 
     * API: http://worldtimeapi.org/api/timezone/Asia/Jakarta
     */
    private function getWorldTime()
    {
        try {
            // Call World Time API for Asia/Jakarta timezone
            $response = Http::timeout(5)->get('http://worldtimeapi.org/api/timezone/Asia/Jakarta');

            if (!$response->successful()) {
                \Log::warning('World Time API failed', ['status' => $response->status()]);
                // Fallback to server time
                return [
                    'method' => 'server',
                    'datetime' => now()->toDateTimeString(),
                    'timezone' => 'Asia/Jakarta',
                    'note' => 'Using server time (API unavailable)'
                ];
            }

            $data = $response->json();
            
            return [
                'method' => 'worldtime_api',
                'datetime' => $data['datetime'] ?? now()->toDateTimeString(),
                'timezone' => $data['timezone'] ?? 'Asia/Jakarta',
                'utc_offset' => $data['utc_offset'] ?? '+07:00',
                'day_of_week' => $data['day_of_week'] ?? null,
                'day_of_year' => $data['day_of_year'] ?? null,
                'week_number' => $data['week_number'] ?? null,
                'abbreviation' => $data['abbreviation'] ?? 'WIB',
            ];

        } catch (\Exception $e) {
            \Log::error('World Time API error', ['message' => $e->getMessage()]);
            // Fallback to server time
            return [
                'method' => 'server',
                'datetime' => now()->toDateTimeString(),
                'timezone' => 'Asia/Jakarta',
                'note' => 'Using server time (API error)'
            ];
        }
    }
}
