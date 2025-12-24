<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Models\Transaction;

class KategoriController extends Controller
{
    /**
     * Display a listing of categories.
     * 
     * Accessible by: All authenticated users
     */
    public function index()
    {
        $categories = Category::withCount('transactions')
            ->orderBy('jenis', 'asc')
            ->orderBy('nama_kategori', 'asc')
            ->get();
        
        // Statistics
        $totalPemasukan = Category::where('jenis', 'Pemasukan')->count();
        $totalPengeluaran = Category::where('jenis', 'Pengeluaran')->count();
        
        return view('kategori.index', compact('categories', 'totalPemasukan', 'totalPengeluaran'));
    }

    /**
     * Show the form for creating a new category.
     * 
     * Accessible by: Admin & Bendahara only
     */
    public function create()
    {
        return view('kategori.create');
    }

    /**
     * Store a newly created category in storage.
     * 
     * Accessible by: Admin & Bendahara only
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:categories,nama_kategori',
            'jenis' => 'required|in:Pemasukan,Pengeluaran',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi',
            'nama_kategori.unique' => 'Nama kategori sudah ada',
            'jenis.required' => 'Jenis kategori wajib dipilih',
            'jenis.in' => 'Jenis harus Pemasukan atau Pengeluaran',
        ]);

        Category::create($validated);

        return redirect()->route('kategori.index')
            ->with('success', "Kategori '{$validated['nama_kategori']}' berhasil ditambahkan!");
    }

    /**
     * Display the specified category.
     * 
     * Accessible by: All authenticated users
     */
    public function show(string $id)
    {
        $category = Category::with(['transactions' => function($query) {
            $query->orderBy('tanggal', 'desc')->take(10);
        }])->findOrFail($id);
        
        // Statistics untuk kategori ini
        $totalTransaksi = $category->transactions()->count();
        $totalNominal = $category->transactions()->sum('nominal');
        
        return view('kategori.show', compact('category', 'totalTransaksi', 'totalNominal'));
    }

    /**
     * Show the form for editing the specified category.
     * 
     * Accessible by: Admin & Bendahara only
     */
    public function edit(string $id)
    {
        $category = Category::findOrFail($id);
        
        return view('kategori.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     * 
     * Accessible by: Admin & Bendahara only
     */
    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);
        
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:categories,nama_kategori,' . $id,
            'jenis' => 'required|in:Pemasukan,Pengeluaran',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi',
            'nama_kategori.unique' => 'Nama kategori sudah ada',
            'jenis.required' => 'Jenis kategori wajib dipilih',
            'jenis.in' => 'Jenis harus Pemasukan atau Pengeluaran',
        ]);

        $category->update($validated);

        return redirect()->route('kategori.index')
            ->with('success', "Kategori '{$validated['nama_kategori']}' berhasil diperbarui!");
    }

    /**
     * Remove the specified category from storage.
     * 
     * Accessible by: Admin & Bendahara only
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        
        // Check if category has transactions
        if ($category->transactions()->count() > 0) {
            return back()->with('error', "Kategori '{$category->nama_kategori}' tidak dapat dihapus karena masih memiliki {$category->transactions()->count()} transaksi!");
        }
        
        $namaKategori = $category->nama_kategori;
        $category->delete();

        return redirect()->route('kategori.index')
            ->with('success', "Kategori '{$namaKategori}' berhasil dihapus!");
    }

    /**
     * Search KBBI API for word definitions
     * 
     * API Endpoint: https://x-labs.my.id/api/kbbi/search/{keyword}
     * Returns: JSON with word definitions for autocomplete suggestions
     */
    public function searchKBBI(Request $request)
    {
        $keyword = $request->get('q');
        
        if (!$keyword || strlen($keyword) < 3) {
            return response()->json([
                'status' => 400,
                'message' => 'Keyword minimal 3 karakter',
                'data' => []
            ]);
        }

        try {
            $response = Http::timeout(60)
                ->withoutVerifying()
                ->get("https://x-labs.my.id/api/kbbi/search/{$keyword}");
            
            if ($response->successful()) {
                $result = $response->json();
                
                // Response structure: {"success":true, "status":200, "message":"...", "data":[{word, type, lem, arti:[{deskripsi, kelas}]}]}
                if (isset($result['success']) && $result['success'] === true && isset($result['data'])) {
                    $suggestions = [];
                    
                    foreach ($result['data'] as $item) {
                        if (isset($item['arti']) && is_array($item['arti']) && count($item['arti']) > 0) {
                            // Ambil deskripsi dari arti
                            $artiTexts = [];
                            foreach (array_slice($item['arti'], 0, 2) as $artiItem) {
                                if (isset($artiItem['deskripsi'])) {
                                    $artiTexts[] = $artiItem['deskripsi'];
                                }
                            }
                            
                            if (count($artiTexts) > 0) {
                                $suggestions[] = [
                                    'kata' => $item['word'] ?? $keyword,
                                    'arti' => implode('; ', $artiTexts)
                                ];
                            }
                        }
                    }
                    
                    if (count($suggestions) > 0) {
                        return response()->json([
                            'status' => 200,
                            'message' => 'Berhasil',
                            'data' => $suggestions
                        ]);
                    }
                }
                
                return response()->json([
                    'status' => 404,
                    'message' => 'Kata tidak ditemukan di KBBI',
                    'data' => []
                ]);
            }
            
            return response()->json([
                'status' => 500,
                'message' => 'Gagal mengambil data dari KBBI: ' . $response->status(),
                'data' => []
            ], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
