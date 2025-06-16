<?php

namespace App\Http\Controllers;

use App\Models\Penanganan;
use App\Http\Resources\PenangananResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

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
     *     path="/api/v1/penanganan",
     *     summary="Get all penanganan records",
     *     tags={"Penanganan"},
     *     security={{"bearerAuth": {}}},
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
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Jika dokter, bisa lihat semua penanganan
            // Jika pasien, hanya bisa lihat penanganan miliknya sendiri
            if ($user->role === 'pasien') {
                $penanganan = Penanganan::with('user')
                    ->where('user_id', $user->id)
                    ->orderBy('tanggal_penanganan', 'desc')
                    ->get();
            } else {
                $penanganan = Penanganan::with('user')
                    ->orderBy('tanggal_penanganan', 'desc')
                    ->get();
            }

            return response()->json([
                'success' => true,
                'message' => 'Data penanganan berhasil diambil',
                'data' => PenangananResource::collection($penanganan)
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
     *     path="/api/v1/penanganan",
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
                'created_by' => Auth::id()
            ]);

            $penanganan->load('user');

            return response()->json([
                'success' => true,
                'message' => 'Data penanganan berhasil dibuat',
                'data' => new PenangananResource($penanganan)
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
     *     path="/api/v1/penanganan/{id}",
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
            
            $penanganan = Penanganan::with('user')->find($id);

            if (!$penanganan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penanganan tidak ditemukan'
                ], 404);
            }

            // Jika pasien, hanya bisa lihat penanganan miliknya sendiri
            if ($user->role === 'pasien' && $penanganan->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data penanganan berhasil diambil',
                'data' => new PenangananResource($penanganan)
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
     *     path="/api/v1/penanganan/{id}",
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
            'tanggal_penanganan' => 'sometimes|required|date',
            'keluhan' => 'sometimes|required|string|max:1000',
            'riwayat_penyakit' => 'nullable|string|max:1000',
            'diagnosis_manual' => 'sometimes|required|string|max:500',
            'telinga_terkena' => 'sometimes|required|in:kiri,kanan,keduanya',
            'tindakan' => 'sometimes|required|string|max:1000'
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

            $penanganan->load('user');

            return response()->json([
                'success' => true,
                'message' => 'Data penanganan berhasil diupdate',
                'data' => new PenangananResource($penanganan)
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
     * @OA\Delete(
     *     path="/api/v1/penanganan/{id}/force-delete",
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