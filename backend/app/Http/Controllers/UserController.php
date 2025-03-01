<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="mail@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User registered successfully"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $token = Auth::guard('api')->attempt([
            'email' => $request->email,
            'password' => $request->password
        ]);

        return response()->json(['message' => 'User registered successfully', 'token' => $token], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update User",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email")
     *         )
     *     ),
     *     @OA\Response(response=200, description="User updated successfully"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->only(['name', 'email']));
        return response()->json(['message' => 'User updated successfully']);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
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
     *     path="/api/me",
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
     *     path="/api/users",
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
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */

    public function getAllUsers()
    {
        $users = User::withTrashed()->get(); // Ambil semua pengguna, termasuk yang dihapus secara soft delete

        return response()->json($users);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
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
     *             @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found")
     * )
     */

    public function show($id)
    {
        $user = User::withTrashed()->findOrFail($id); // Temukan pengguna, termasuk yang dihapus secara soft delete

        return response()->json($user);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}/force-delete",
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
        $user = User::withTrashed()->findOrFail($id);
        $user->forceDelete(); // Permanent delete

        return response()->json(['message' => 'User permanently deleted successfully']);
    }

    /**
     * @OA\Post(
     *     path="/api/users/{id}/restore",
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
