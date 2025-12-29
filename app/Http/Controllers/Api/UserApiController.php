<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * REST API Controller untuk User Management
 * 
 * Menyediakan endpoint GET untuk mengakses data user
 * melalui REST API. Hanya dapat diakses oleh Admin.
 */
class UserApiController extends Controller
{
    /**
     * Get all users with roles and statistics
     * 
     * GET /api/users
     * 
     * Query Parameters:
     * - role_id: Filter by role ID
     * - search: Search by name or email
     * - per_page: Number of items per page (default: 20)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::with('role:id,nama_role')
                ->orderBy('created_at', 'desc');
            
            // Filter by role
            if ($request->filled('role_id')) {
                $query->where('role_id', $request->role_id);
            }
            
            // Search by name or email
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }
            
            // Pagination
            $perPage = $request->get('per_page', 20);
            $users = $query->paginate($perPage);
            
            // Format user data (hide sensitive info)
            $formattedUsers = collect($users->items())->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ? [
                        'id' => $user->role->id,
                        'nama_role' => $user->role->nama_role,
                    ] : null,
                    'alamat' => [
                        'provinsi' => $user->provinsi_nama,
                        'kabkota' => $user->kabkota_nama,
                        'kecamatan' => $user->kecamatan_nama,
                        'kelurahan' => $user->kelurahan_nama,
                        'kode_pos' => $user->kode_pos,
                    ],
                    'created_at' => $user->created_at,
                    'formatted_created_at' => $user->created_at->format('d M Y H:i'),
                ];
            });
            
            // Statistics
            $stats = [
                'total_users' => User::count(),
                'by_role' => Role::withCount('users')->get()->mapWithKeys(function($role) {
                    return [$role->nama_role => $role->users_count];
                }),
            ];
            
            // Get roles for filter
            $roles = Role::all(['id', 'nama_role']);
            
            return response()->json([
                'success' => true,
                'message' => 'Data user berhasil diambil',
                'data' => $formattedUsers,
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
                'statistics' => $stats,
                'roles' => $roles,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data user: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Get single user by ID
     * 
     * GET /api/users/{id}
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $user = User::with('role:id,nama_role')->findOrFail($id);
            
            // Get user transaction statistics
            $transactionStats = [
                'total_transaksi' => $user->transactions()->count(),
                'total_pemasukan' => (float) $user->transactions()
                    ->whereHas('category', fn($q) => $q->where('jenis', 'Pemasukan'))
                    ->sum('nominal'),
                'total_pengeluaran' => (float) $user->transactions()
                    ->whereHas('category', fn($q) => $q->where('jenis', 'Pengeluaran'))
                    ->sum('nominal'),
            ];
            
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ? [
                    'id' => $user->role->id,
                    'nama_role' => $user->role->nama_role,
                ] : null,
                'alamat' => [
                    'provinsi_id' => $user->provinsi_id,
                    'provinsi_nama' => $user->provinsi_nama,
                    'kabkota_id' => $user->kabkota_id,
                    'kabkota_nama' => $user->kabkota_nama,
                    'kecamatan_id' => $user->kecamatan_id,
                    'kecamatan_nama' => $user->kecamatan_nama,
                    'kelurahan_id' => $user->kelurahan_id,
                    'kelurahan_nama' => $user->kelurahan_nama,
                    'kode_pos' => $user->kode_pos,
                ],
                'alamat_lengkap' => $this->formatAlamatLengkap($user),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'formatted_created_at' => $user->created_at->format('d F Y H:i'),
                'transaction_stats' => $transactionStats,
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Detail user berhasil diambil',
                'data' => $userData,
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail user: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Format alamat lengkap
     */
    private function formatAlamatLengkap(User $user): string
    {
        $parts = array_filter([
            $user->kelurahan_nama,
            $user->kecamatan_nama,
            $user->kabkota_nama,
            $user->provinsi_nama,
            $user->kode_pos,
        ]);
        
        return implode(', ', $parts) ?: '-';
    }
}
