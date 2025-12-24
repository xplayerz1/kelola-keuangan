<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Role;

class AdminController extends Controller
{
    /**
     * Base URL API Wilayah Indonesia
     */
    private $apiBaseUrl = 'https://alamat.thecloudalert.com/api';

    /**
     * Display a listing of users with their roles.
     * 
     * MENAMPILKAN RELASI MINIMAL 2 TABEL: users JOIN roles
     */
    public function index()
    {
        // Eager loading untuk menampilkan relasi User dengan Role
        $users = User::with('role')->orderBy('created_at', 'desc')->get();
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     * 
     * INTEGRASI API: Mengambil data wilayah Indonesia (Provinsi)
     */
    public function create()
    {
        $roles = Role::all();
        
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role_id' => 'required|exists:roles,id',
            'provinsi_id' => 'nullable|string',
            'provinsi_nama' => 'nullable|string',
            'kabkota_id' => 'nullable|string',
            'kabkota_nama' => 'nullable|string',
            'kecamatan_id' => 'nullable|string',
            'kecamatan_nama' => 'nullable|string',
            'kelurahan_id' => 'nullable|string',
            'kelurahan_nama' => 'nullable|string',
            'kode_pos' => 'nullable|string',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'provinsi_id' => $validated['provinsi_id'] ?? null,
            'provinsi_nama' => $validated['provinsi_nama'] ?? null,
            'kabkota_id' => $validated['kabkota_id'] ?? null,
            'kabkota_nama' => $validated['kabkota_nama'] ?? null,
            'kecamatan_id' => $validated['kecamatan_id'] ?? null,
            'kecamatan_nama' => $validated['kecamatan_nama'] ?? null,
            'kelurahan_id' => $validated['kelurahan_id'] ?? null,
            'kelurahan_nama' => $validated['kelurahan_nama'] ?? null,
            'kode_pos' => $validated['kode_pos'] ?? null,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    /**
     * Display the specified user.
     */
    public function show(string $id)
    {
        $user = User::with('role', 'transactions')->findOrFail($id);
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id',
            'provinsi_id' => 'nullable|string',
            'provinsi_nama' => 'nullable|string',
            'kabkota_id' => 'nullable|string',
            'kabkota_nama' => 'nullable|string',
            'kecamatan_id' => 'nullable|string',
            'kecamatan_nama' => 'nullable|string',
            'kelurahan_id' => 'nullable|string',
            'kelurahan_nama' => 'nullable|string',
            'kode_pos' => 'nullable|string',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'provinsi_id' => $validated['provinsi_id'] ?? null,
            'provinsi_nama' => $validated['provinsi_nama'] ?? null,
            'kabkota_id' => $validated['kabkota_id'] ?? null,
            'kabkota_nama' => $validated['kabkota_nama'] ?? null,
            'kecamatan_id' => $validated['kecamatan_id'] ?? null,
            'kecamatan_nama' => $validated['kecamatan_nama'] ?? null,
            'kelurahan_id' => $validated['kelurahan_id'] ?? null,
            'kelurahan_nama' => $validated['kelurahan_nama'] ?? null,
            'kode_pos' => $validated['kode_pos'] ?? null,
        ]);

        // Update password jika diisi
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui!');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }
        
        $user->delete();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus!');
    }

    /**
     * Change user role (promote Viewer to Bendahara, etc.)
     */
    public function changeRole(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);
        
        $oldRole = $user->role->nama_role;
        $user->update(['role_id' => $validated['role_id']]);
        $newRole = $user->role->nama_role;
        
        return redirect()->route('admin.users.index')
            ->with('success', "Role {$user->name} berhasil diubah dari {$oldRole} menjadi {$newRole}!");
    }

    /**
     * API Endpoints untuk AJAX requests
     * Menggunakan API Wilayah Indonesia dari thecloudalert.com
     */

    /**
     * Get provinces (Provinsi)
     */
    public function getProvinces()
    {
        try {
            $response = Http::timeout(30)
                ->withoutVerifying()
                ->get("{$this->apiBaseUrl}/provinsi/get/");
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            return response()->json(['status' => 500, 'message' => 'Gagal mengambil data provinsi'], 500);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get cities (Kabupaten/Kota) by Province ID
     */
    public function getCities(Request $request)
    {
        $provinsiId = $request->get('d_provinsi_id');
        
        if (!$provinsiId) {
            return response()->json(['status' => 400, 'message' => 'Province ID required'], 400);
        }

        try {
            $response = Http::timeout(30)
                ->withoutVerifying()
                ->get("{$this->apiBaseUrl}/kabkota/get/", [
                    'd_provinsi_id' => $provinsiId
                ]);
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            return response()->json(['status' => 500, 'message' => 'Gagal mengambil data kota'], 500);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get districts (Kecamatan) by City ID
     */
    public function getDistricts(Request $request)
    {
        $kabkotaId = $request->get('d_kabkota_id');
        
        if (!$kabkotaId) {
            return response()->json(['status' => 400, 'message' => 'City ID required'], 400);
        }

        try {
            $response = Http::timeout(30)
                ->withoutVerifying()
                ->get("{$this->apiBaseUrl}/kecamatan/get/", [
                    'd_kabkota_id' => $kabkotaId
                ]);
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            return response()->json(['status' => 500, 'message' => 'Gagal mengambil data kecamatan'], 500);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get sub-districts (Kelurahan) by District ID
     */
    public function getSubDistricts(Request $request)
    {
        $kecamatanId = $request->get('d_kecamatan_id');
        
        if (!$kecamatanId) {
            return response()->json(['status' => 400, 'message' => 'District ID required'], 400);
        }

        try {
            $response = Http::timeout(30)
                ->withoutVerifying()
                ->get("{$this->apiBaseUrl}/kelurahan/get/", [
                    'd_kecamatan_id' => $kecamatanId
                ]);
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            return response()->json(['status' => 500, 'message' => 'Gagal mengambil data kelurahan'], 500);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get postal codes (Kode Pos) by City and District ID
     */
    public function getPostalCodes(Request $request)
    {
        $kabkotaId = $request->get('d_kabkota_id');
        $kecamatanId = $request->get('d_kecamatan_id');
        
        if (!$kabkotaId || !$kecamatanId) {
            return response()->json(['status' => 400, 'message' => 'City and District ID required'], 400);
        }

        try {
            $response = Http::timeout(30)
                ->withoutVerifying()
                ->get("{$this->apiBaseUrl}/kodepos/get/", [
                    'd_kabkota_id' => $kabkotaId,
                    'd_kecamatan_id' => $kecamatanId
                ]);
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            return response()->json(['status' => 500, 'message' => 'Gagal mengambil data kode pos'], 500);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
        }
    }
}
