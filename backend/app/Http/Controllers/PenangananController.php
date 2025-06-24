<?php

namespace App\Http\Controllers;

use App\Models\Penanganan;
use App\Models\User;
use App\Http\Resources\PenangananResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * @OA\Tag(
 *     name="Penanganan",
 *     description="Penanganan management endpoints",
 *     x={"order": 2}
 * )
 */
class PenangananController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/penanganan",
     *     summary="Get all penanganan records",
     *     tags={"Penanganan"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filter penanganan by created date (format: YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of penanganan records",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data penanganan berhasil diambil"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Penanganan"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $dateFilter = $request->query('date');

            // Jika dokter, bisa lihat semua penanganan
            // Jika pasien, hanya bisa lihat penanganan yang di-assign ke dia
            if ($user->hasRole(('pasien'))) {
                $query = Penanganan::with(['user', 'createdByUser'])
                    ->where('assigned_to', $user->id)
                    ->where('status', 'assigned');
            } else {
                $query = Penanganan::with(['user', 'createdByUser']);
            }

            // Apply date filter if provided
            if ($dateFilter) {
                $query->whereDate('created_at', $dateFilter);
            }

            $penanganan = $query->orderBy('tanggal_penanganan', 'desc')->get();

            // Format data seperti VideoController
            $formattedData = $penanganan->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'tanggal_penanganan' => $item->tanggal_penanganan,
                    'keluhan' => $item->keluhan,
                    'riwayat_penyakit' => $item->riwayat_penyakit,
                    'diagnosis_manual' => $item->diagnosis_manual,
                    'telinga_terkena' => $item->telinga_terkena,
                    'tindakan' => $item->tindakan,
                    'status' => $item->status,
                    'created_by' => $item->created_by,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    // Relasi data
                    'pasien' => $item->user ? [
                        'name' => $item->user->name,
                        'role' => $item->user->getRoleNames()->first()
                    ] : null,
                    'created_by_user' => $item->createdByUser ? [
                        'name' => $item->createdByUser->name,
                        'role' => $item->createdByUser->getRoleNames()->first()
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Data penanganan berhasil diambil',
                'data' => $formattedData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data penanganan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/v1/penanganan",
     *     summary="Create new penanganan record",
     *     tags={"Penanganan"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "tanggal_penanganan", "keluhan", "diagnosis_manual", "telinga_terkena", "tindakan"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="tanggal_penanganan", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="keluhan", type="string", example="Telinga terasa sakit dan berdenging"),
     *             @OA\Property(property="riwayat_penyakit", type="string", example="Pernah mengalami infeksi telinga"),
     *             @OA\Property(property="diagnosis_manual", type="string", example="Otitis Media Akut"),
     *             @OA\Property(property="telinga_terkena", type="string", enum={"kiri", "kanan", "keduanya"}, example="kiri"),
     *             @OA\Property(property="tindakan", type="string", example="Pemberian antibiotik dan analgesik")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Penanganan record created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data penanganan berhasil dibuat"),
     *             @OA\Property(property="data", ref="#/components/schemas/Penanganan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'tanggal_penanganan' => 'required|date',
            'keluhan' => 'required|string|max:1000',
            'riwayat_penyakit' => 'nullable|string|max:1000',
            'diagnosis_manual' => 'required|string|max:500',
            'telinga_terkena' => 'required|in:kiri,kanan,keduanya',
            'tindakan' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $penanganan = Penanganan::create([
                'user_id' => $request->user_id,
                'tanggal_penanganan' => $request->tanggal_penanganan,
                'keluhan' => $request->keluhan,
                'riwayat_penyakit' => $request->riwayat_penyakit,
                'diagnosis_manual' => $request->diagnosis_manual,
                'telinga_terkena' => $request->telinga_terkena,
                'tindakan' => $request->tindakan,
                'status' => 'assigned',
                'created_by' => Auth::id()
            ]);

            $penanganan->load(['user', 'createdByUser']);

            // Format data seperti VideoController
            $formattedData = [
                'id' => $penanganan->id,
                'user_id' => $penanganan->user_id,
                'tanggal_penanganan' => $penanganan->tanggal_penanganan,
                'keluhan' => $penanganan->keluhan,
                'riwayat_penyakit' => $penanganan->riwayat_penyakit,
                'diagnosis_manual' => $penanganan->diagnosis_manual,
                'telinga_terkena' => $penanganan->telinga_terkena,
                'tindakan' => $penanganan->tindakan,
                'status' => $penanganan->status,
                'assigned_to' => $penanganan->assigned_to,
                'created_by' => $penanganan->created_by,
                'created_at' => $penanganan->created_at,
                'updated_at' => $penanganan->updated_at,
                // Relasi data
                'pasien' => $penanganan->user ? [
                    'name' => $penanganan->user->name,
                    'role' => $penanganan->user->getRoleNames()->first()
                ] : null,
                'created_by_user' => $penanganan->createdByUser ? [
                    'name' => $penanganan->createdByUser->name,
                    'role' => $penanganan->createdByUser->getRoleNames()->first()
                ] : null,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data penanganan berhasil dibuat',
                'data' => $formattedData
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat data penanganan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/v1/penanganan/{id}",
     *     summary="Get specific penanganan record",
     *     tags={"Penanganan"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Penanganan record details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data penanganan berhasil diambil"),
     *             @OA\Property(property="data", ref="#/components/schemas/Penanganan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Penanganan not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Data penanganan tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $user = Auth::user();

            $penanganan = Penanganan::with(['user', 'createdByUser'])->find($id);

            if (!$penanganan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penanganan tidak ditemukan'
                ], 404);
            }

            // Jika pasien, hanya bisa lihat penanganan yang di-assign ke dia
            if ($user->hasRole('pasien') && $penanganan->assigned_to !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak'
                ], 403);
            }

            // Format data seperti VideoController
            $formattedData = [
                'id' => $penanganan->id,
                'user_id' => $penanganan->user_id,
                'tanggal_penanganan' => $penanganan->tanggal_penanganan,
                'keluhan' => $penanganan->keluhan,
                'riwayat_penyakit' => $penanganan->riwayat_penyakit,
                'diagnosis_manual' => $penanganan->diagnosis_manual,
                'telinga_terkena' => $penanganan->telinga_terkena,
                'tindakan' => $penanganan->tindakan,
                'status' => $penanganan->status,
                'created_by' => $penanganan->created_by,
                'created_at' => $penanganan->created_at,
                'updated_at' => $penanganan->updated_at,
                // Relasi data
                'pasien' => $penanganan->user ? [
                    'id' => $penanganan->user->id,
                    'name' => $penanganan->user->name,
                    'kode_akses' => $penanganan->user->kode_akses,
                    'role' => $penanganan->user->getRoleNames()->first()
                ] : null,
                'created_by_user' => $penanganan->createdByUser ? [
                    'id' => $penanganan->createdByUser->id,
                    'name' => $penanganan->createdByUser->name,
                    'kode_akses' => $penanganan->createdByUser->kode_akses,
                    'role' => $penanganan->createdByUser->getRoleNames()->first()
                ] : null,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data penanganan berhasil diambil',
                'data' => $formattedData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data penanganan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/v1/penanganan/{id}",
     *     summary="Update penanganan record",
     *     tags={"Penanganan"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="tanggal_penanganan", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="keluhan", type="string", example="Telinga terasa sakit dan berdenging"),
     *             @OA\Property(property="riwayat_penyakit", type="string", example="Pernah mengalami infeksi telinga"),
     *             @OA\Property(property="diagnosis_manual", type="string", example="Otitis Media Akut"),
     *             @OA\Property(property="telinga_terkena", type="string", enum={"kiri", "kanan", "keduanya"}, example="kiri"),
     *             @OA\Property(property="tindakan", type="string", example="Pemberian antibiotik dan analgesik")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Penanganan record updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data penanganan berhasil diupdate"),
     *             @OA\Property(property="data", ref="#/components/schemas/Penanganan")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tanggal_penanganan' => 'nullable|date',
            'keluhan' => 'nullable|string|max:1000',
            'riwayat_penyakit' => 'nullable|string|max:1000',
            'diagnosis_manual' => 'nullable|string|max:500',
            'telinga_terkena' => 'nullable|in:kiri,kanan,keduanya',
            'tindakan' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $penanganan = Penanganan::find($id);

            if (!$penanganan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penanganan tidak ditemukan'
                ], 404);
            }

            $penanganan->update($request->only([
                'tanggal_penanganan',
                'keluhan',
                'riwayat_penyakit',
                'diagnosis_manual',
                'telinga_terkena',
                'tindakan'
            ]));

            $penanganan->load(['user', 'createdByUser']);

            // Format data seperti VideoController
            $formattedData = [
                'id' => $penanganan->id,
                'user_id' => $penanganan->user_id,
                'tanggal_penanganan' => $penanganan->tanggal_penanganan,
                'keluhan' => $penanganan->keluhan,
                'riwayat_penyakit' => $penanganan->riwayat_penyakit,
                'diagnosis_manual' => $penanganan->diagnosis_manual,
                'telinga_terkena' => $penanganan->telinga_terkena,
                'tindakan' => $penanganan->tindakan,
                'status' => $penanganan->status,
                'created_by' => $penanganan->created_by,
                'created_at' => $penanganan->created_at,
                'updated_at' => $penanganan->updated_at,
                // Relasi data
                'pasien' => $penanganan->user ? [
                    'name' => $penanganan->user->name,
                    'role' => $penanganan->user->getRoleNames()->first()
                ] : null,
                'created_by_user' => $penanganan->createdByUser ? [
                    'name' => $penanganan->createdByUser->name,
                    'role' => $penanganan->createdByUser->getRoleNames()->first()
                ] : null,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data penanganan berhasil diupdate',
                'data' => $formattedData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate data penanganan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/v1/penanganan/{id}/assign/{userId}",
     *     summary="Assign penanganan to user",
     *     tags={"Penanganan"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Penanganan ID"
     *     ),
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="User ID to assign to"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Penanganan assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Penanganan assigned to user successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Penanganan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Penanganan or User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Penanganan tidak ditemukan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="User is not a patient",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User bukan pasien")
     *         )
     *     )
     * )
     */
    public function assignToUser($id, $userId): JsonResponse
    {
        try {
            // Logging awal saat method dipanggil
            Log::info("Assigning penangananId: $id to userId: $userId");

            // Cari penanganan
            $penanganan = Penanganan::find($id);
            if (!$penanganan) {
                Log::warning("Penanganan tidak ditemukan untuk ID: $id");
                return response()->json([
                    'success' => false,
                    'message' => 'Penanganan tidak ditemukan'
                ], 404);
            }

            // Cari user
            $user = User::find($userId);
            if (!$user) {
                Log::warning("User tidak ditemukan untuk ID: $userId");
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            // Validasi role user
            if (!($user->hasRole('pasien'))) {
                Log::warning("User ID $userId bukan pasien, tapi " . $user->getRoleNames()->first());
                return response()->json([
                    'success' => false,
                    'message' => 'User bukan pasien'
                ], 400);
            }

            // Update penanganan
            $penanganan->update([
                'user_id' => $userId,
                'status' => 'assigned',
            ]);

            // Load relasi untuk response
            $penanganan->load(['user', 'createdByUser']);

            Log::info("Penanganan ID $id berhasil di-assign ke User ID $userId");

            // Format data seperti method lainnya
            $formattedData = [
                'id' => $penanganan->id,
                'user_id' => $penanganan->user_id,
                'tanggal_penanganan' => $penanganan->tanggal_penanganan,
                'keluhan' => $penanganan->keluhan,
                'riwayat_penyakit' => $penanganan->riwayat_penyakit,
                'diagnosis_manual' => $penanganan->diagnosis_manual,
                'telinga_terkena' => $penanganan->telinga_terkena,
                'tindakan' => $penanganan->tindakan,
                'status' => $penanganan->status,
                'created_by' => $penanganan->created_by,
                'created_at' => $penanganan->created_at,
                'updated_at' => $penanganan->updated_at,
                // Relasi data
                'pasien' => $penanganan->user ? [
                    'name' => $penanganan->user->name,
                    'role' => $penanganan->user->getRoleNames()->first()
                ] : null,
                'created_by_user' => $penanganan->createdByUser ? [
                    'name' => $penanganan->createdByUser->name,
                    'role' => $penanganan->createdByUser->getRoleNames()->first()
                ] : null,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Penanganan assigned to user successfully',
                'data' => $formattedData
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error assigning penanganan: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal assign penanganan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/v1/penanganan/{id}",
     *     summary="Unassign penanganan",
     *     tags={"Penanganan"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Penanganan ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Penanganan unassigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Penanganan unassigned successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Penanganan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Penanganan not found"
     *     )
     * )
     */
    public function updateStatusPenanganan($id)
    {
        // Temukan penanganan berdasarkan ID
        $penanganan = Penanganan::findOrFail($id);

        // Update status dan hapus assigned_to - sama seperti VideoController
        $penanganan->update([
            'status' => 'unassigned',
            'user_id' => null,
        ]);

        return response()->json([
            'message' => 'Penanganan unassigned successfully',
            'data' => $penanganan,
        ]);
    }
    /**
     * @OA\Delete(
     *     path="/v1/penanganan/{id}",
     *     summary="Delete penanganan record",
     *     tags={"Penanganan"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Penanganan record deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data penanganan berhasil dihapus")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Penanganan not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Data penanganan tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function delete($id): JsonResponse
    {
        try {
            $penanganan = Penanganan::find($id);

            if (!$penanganan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penanganan tidak ditemukan'
                ], 404);
            }

            $penanganan->delete(); // Ini sekarang akan soft delete

            return response()->json([
                'success' => true,
                'message' => 'Data penanganan berhasil dihapus sementara'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data penanganan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/v1/penanganan/{id}/restore",
     *     summary="Restore soft deleted penanganan record",
     *     tags={"Penanganan"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Penanganan record restored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data penanganan berhasil dipulihkan"),
     *             @OA\Property(property="data", ref="#/components/schemas/Penanganan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Penanganan not found in trash",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Data penanganan tidak ditemukan di trash")
     *         )
     *     )
     * )
     */
    public function restore($id): JsonResponse
    {
        try {
            $penanganan = Penanganan::onlyTrashed()->find($id);

            if (!$penanganan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penanganan tidak ditemukan di trash'
                ], 404);
            }

            $penanganan->restore();
            $penanganan->load(['user', 'assignedToUser', 'createdByUser']);

            // Format data seperti method lainnya
            $formattedData = [
                'id' => $penanganan->id,
                'user_id' => $penanganan->user_id,
                'tanggal_penanganan' => $penanganan->tanggal_penanganan,
                'keluhan' => $penanganan->keluhan,
                'riwayat_penyakit' => $penanganan->riwayat_penyakit,
                'diagnosis_manual' => $penanganan->diagnosis_manual,
                'telinga_terkena' => $penanganan->telinga_terkena,
                'tindakan' => $penanganan->tindakan,
                'status' => $penanganan->status,
                'assigned_to' => $penanganan->assigned_to,
                'created_by' => $penanganan->created_by,
                'created_at' => $penanganan->created_at,
                'updated_at' => $penanganan->updated_at,
                // Relasi data
                'user' => $penanganan->user ? [
                    'id' => $penanganan->user->id,
                    'name' => $penanganan->user->name,
                    'kode_akses' => $penanganan->user->kode_akses,
                    'role' => $penanganan->user->getRoleNames()->first()
                ] : null,
                'assigned_to_user' => $penanganan->assignedToUser ? [
                    'id' => $penanganan->assignedToUser->id,
                    'name' => $penanganan->assignedToUser->name,
                    'kode_akses' => $penanganan->assignedToUser->kode_akses,
                    'role' => $penanganan->assignedToUser->getRoleNames()->first()
                ] : null,
                'created_by_user' => $penanganan->createdByUser ? [
                    'id' => $penanganan->createdByUser->id,
                    'name' => $penanganan->createdByUser->name,
                    'kode_akses' => $penanganan->createdByUser->kode_akses,
                    'role' => $penanganan->createdByUser->getRoleNames()->first()
                ] : null,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data penanganan berhasil dipulihkan',
                'data' => $formattedData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan data penanganan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/v1/penanganan/{id}/soft-delete",
     *     summary="Soft delete penanganan record",
     *     tags={"Penanganan"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Penanganan record soft deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data penanganan berhasil dihapus sementara")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Penanganan not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Data penanganan tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function softDelete($id): JsonResponse
    {
        try {
            $penanganan = Penanganan::find($id);

            if (!$penanganan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penanganan tidak ditemukan'
                ], 404);
            }

            $penanganan->delete(); // Ini akan melakukan soft delete karena SoftDeletes trait

            return response()->json([
                'success' => true,
                'message' => 'Data penanganan berhasil dihapus sementara'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data penanganan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/v1/penanganan/{id}/force-delete",
     *     summary="Force delete penanganan record",
     *     tags={"Penanganan"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Penanganan record deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data penanganan berhasil dihapus")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Penanganan not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Data penanganan tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function forceDelete($id): JsonResponse
    {
        try {
            $penanganan = Penanganan::find($id);

            if (!$penanganan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penanganan tidak ditemukan'
                ], 404);
            }

            $penanganan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data penanganan berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data penanganan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}