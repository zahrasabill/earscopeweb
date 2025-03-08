<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

/**
 * @OA\Tag(
 *     name="User",
 *     description="User management endpoints",
 *     x={"order": 2}
 * )
 */

class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/v1/register",
     *     summary="Register a new user",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "tanggal_lahir"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="tanggal_lahir", type="string", format="date", example="2012-12-31"),
     *         )
     *     ),
     *     @OA\Response(response=201, description="User registered successfully"),
     *     @OA\Response(
     *       response=400,
    *        description="Validation error",
    *        @OA\JsonContent(
    *            type="object",
    *            @OA\Property(property="message", type="string", example="The given data was invalid."),
    *            @OA\Property(
    *                property="errors",
    *                type="object",
    *                @OA\Property(
    *                    property="name",
    *                    type="array",
    *                    @OA\Items(type="string", example="Nama sudah terdaftar, silakan gunakan nama lain.")
    *                )
    *            )
    *        )
    *    )
     * )
     */
    public function register(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255|unique:users,name',
            'tanggal_lahir' => 'required|date|before_or_equal:' . now()->toDateString(), // Pastikan tanggal lahir tidak melebihi hari ini
        ],[
            'name.unique' => 'Nama sudah terdaftar, silakan gunakan nama lain.',   
        ]);

        // Hitung usia berdasarkan tanggal lahir
        $tanggal_lahir = Carbon::parse($request->tanggal_lahir);
        $usia = $tanggal_lahir->age; // Menghitung usia

        // Generate password otomatis (contoh: 8 karakter acak)
        $password = Str::random(8);

        // Generate kode_akses unik (contoh: ERS-X7Y9Z)
        $kode_akses = 'ERS-' . Str::upper(Str::random(5));

        // Simpan data pengguna ke database
        $user = User::create([
            'kode_akses' => $kode_akses,
            'name' => $request->name,
            'tanggal_lahir' => $request->tanggal_lahir,
            'usia' => $usia,
            'no_ktp' => null, // Nomor KTP tidak wajib jika tidak digunakan
            'email' => null, // Email tidak wajib jika tidak digunakan
            'password' => Hash::make($password), // Hash password sebelum disimpan
        ]);

        // Auto assign role 'user'
        $userRole = Role::findByName('user', 'api'); // Ambil role 'user' dari database
        $user->assignRole($userRole); // Tetapkan role 'user' ke pengguna

        // Kirim respons dengan informasi login
        return response()->json([
            'message' => 'User registered successfully',
            'data' => [
                'kode_akses' => $kode_akses,
                'password' => $password, // Kirim password plain sebagai respons
            ]
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/v1/users/{id}",
     *     summary="Update User",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe Updated"),
     *             @OA\Property(property="email", type="string", format="email", example="new.email@example.com"),
     *             @OA\Property(property="no_ktp", type="string", example="1234567890123456"),
     *             @OA\Property(property="tanggal_lahir", type="string", format="date", example="1995-05-15"),
     *             @OA\Property(property="role", type="string", enum={"user", "dokter", "admin"}, example="dokter"),
     *             @OA\Property(property="password", type="string", minLength=8, maxLength=8, example="newpassword")
     *         )
     *     ),
     *     @OA\Response(response=200, description="User updated successfully"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */

    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'name' => 'nullable|string|max:255', // Opsional
            'email' => 'nullable|email|unique:users,email,' . $id, // Email unik, kecuali untuk pengguna saat ini
            'no_ktp' => 'nullable|numeric|digits:16', // No KTP numerik dan tepat 16 digit
            'tanggal_lahir' => 'nullable|date|before_or_equal:' . now()->toDateString(), // Tanggal lahir opsional
            'role' => 'nullable|string|in:user,dokter,admin', // Role opsional
            'password' => 'nullable|string|min:8|max:8', // Password opsional, tetapi harus 8 karakter
        ]);

        // Temukan pengguna berdasarkan ID
        $user = User::findOrFail($id);

        // Data untuk diperbarui
        $data = [];

        if ($request->has('name')) {
            $data['name'] = $request->name;
        }

        if ($request->has('email')) {
            $data['email'] = $request->email;
        }

        if ($request->has('no_ktp')) {
            $data['no_ktp'] = $request->no_ktp;
        }

        if ($request->has('tanggal_lahir')) {
            $tanggal_lahir = Carbon::parse($request->tanggal_lahir);
            $data['tanggal_lahir'] = $request->tanggal_lahir;
            $data['usia'] = $tanggal_lahir->age; // Hitung usia baru
        }

        if ($request->has('password')) {
            $data['password'] = Hash::make($request->password); // Hash password baru
        }

        // Update data pengguna
        $user->update($data);

        // Update role jika ada
        if ($request->has('role')) {
            $roleName = $request->role;
            $role = Role::findByName($roleName, 'api'); // Ambil role dari database
            $user->syncRoles([$role]); // Tetapkan role baru
        }

        // Kirim respons berhasil
        return response()->json(['message' => 'User updated successfully']);
    }

    /**
     * @OA\Delete(
     *     path="/v1/users/{id}",
     *     summary="Delete User",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="User deleted successfully"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * @OA\Get(
     *     path="/v1/me",
     *     summary="Get authenticated user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Authenticated user data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */

    public function me()
    {
        return response()->json(Auth::guard('api')->user());
    }

    /**
     * @OA\Get(
     *     path="/v1/users",
     *     summary="Get all users",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of all users",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="roles", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function getAllUsers()
    {
        // Ambil semua pengguna beserta role mereka
        $users = User::withTrashed()
            ->with('roles') // Muat relasi roles
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'tanggal_lahir' => $user->tanggal_lahir,
                    'usia' => $user->usia,
                    'no_ktp' => $user->no_ktp,
                    'roles' => $user->roles->pluck('name'), // Ambil nama role
                    'deleted_at' => $user->deleted_at,
                ];
            });

        return response()->json($users);
    }

    /**
     * @OA\Get(
     *     path="/v1/users/{id}",
     *     summary="Get user by ID",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="User details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function show($id)
    {
        // Temukan pengguna berdasarkan ID, termasuk yang dihapus secara soft delete
        $user = User::withTrashed()
            ->with('roles') // Muat relasi roles
            ->findOrFail($id);

        // Format respons
        $response = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'tanggal_lahir' => $user->tanggal_lahir,
            'usia' => $user->usia,
            'no_ktp' => $user->no_ktp,
            'roles' => $user->roles->pluck('name'), // Ambil nama role
            'deleted_at' => $user->deleted_at,
        ];

        return response()->json($response);
    }

    /**
     * @OA\Delete(
     *     path="/v1/users/{id}/force-delete",
     *     summary="Permanently delete a user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="User permanently deleted successfully"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */

    public function forceDelete($id)
    {
        try {
            Log::info('Attempting to force delete user with ID: ' . $id);

            $user = User::withTrashed()->findOrFail($id);
            Log::info('User found: ' . json_encode($user));

            $user->forceDelete();
            Log::info('User permanently deleted successfully');

            return response()->json(['message' => 'User permanently deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error in forceDelete: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/v1/users/{id}/restore",
     *     summary="Restore a soft deleted user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="User restored successfully"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */

    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore(); // Restore user

        return response()->json(['message' => 'User restored successfully']);
    }
}
