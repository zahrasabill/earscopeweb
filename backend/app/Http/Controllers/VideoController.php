<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    /**
     * @OA\Post(
     *     path="/v1/videos",
     *     summary="Upload a pair of videos (raw and processed)",
     *     tags={"Videos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Upload raw and processed videos",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"raw_video", "processed_video"},
     *                 @OA\Property(
     *                     property="raw_video",
     *                     type="string",
     *                     format="binary",
     *                     description="Raw video file"
     *                 ),
     *                 @OA\Property(
     *                     property="processed_video",
     *                     type="string",
     *                     format="binary",
     *                     description="Processed video file with bounding box"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Videos uploaded successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="raw_video_path", type="string"),
     *                 @OA\Property(property="processed_video_path", type="string"),
     *                 @OA\Property(property="status", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'raw_video' => 'required|file|mimetypes:video/mp4,video/quicktime',
            'processed_video' => 'required|file|mimetypes:video/mp4,video/quicktime',
        ]);

        $timestamp = now()->format('Ymd_His');
        $folderPath = "videos/{$timestamp}";

        // Simpan video mentah
        $rawVideoFilename = "raw_{$timestamp}.mp4";
        $rawVideoPath = $request->file('raw_video')->storeAs($folderPath, $rawVideoFilename, 'private');
        $rawVideoFullPath = storage_path("app/private/{$rawVideoPath}");

        // Simpan video dengan bounding box
        $processedVideoFilename = "bb_{$timestamp}.mp4";
        $processedVideoPath = $request->file('processed_video')->storeAs($folderPath, $processedVideoFilename, 'private');
        $processedVideoFullPath = storage_path("app/private/{$processedVideoPath}");

        // Konversi ke H.264
        $convertedRawVideoFilename = "raw_{$timestamp}_h264.mp4";
        $convertedRawVideoPath = storage_path("app/private/{$folderPath}/{$convertedRawVideoFilename}");

        $convertedProcessedVideoFilename = "bb_{$timestamp}_h264.mp4";
        $convertedProcessedVideoPath = storage_path("app/private/{$folderPath}/{$convertedProcessedVideoFilename}");

        // Jalankan FFmpeg untuk konversi
        $this->convertToH264($rawVideoFullPath, $convertedRawVideoPath);
        $this->convertToH264($processedVideoFullPath, $convertedProcessedVideoPath);

        // Simpan data ke database
        $video = Video::create([
            'raw_video_path' => "videos/{$timestamp}/{$convertedRawVideoFilename}",
            'processed_video_path' => "videos/{$timestamp}/{$convertedProcessedVideoFilename}",
            'status' => 'unassigned',
        ]);

        return response()->json([
            'message' => 'Videos uploaded and converted successfully',
            'data' => $video,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/v1/videos/{videoId}/assign/{userId}",
     *     summary="Assign a video to a user",
     *     tags={"Videos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="videoId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="userId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Video assigned successfully"),
     *     @OA\Response(response=404, description="Video or User not found")
     * )
     */
    public function assignToUser($videoId, $userId)
    {
        $video = Video::findOrFail($videoId);
        $video->update([
            'user_id' => $userId,
            'status' => 'assigned',
        ]);

        return response()->json([
            'message' => 'Video assigned to user successfully',
            'data' => $video,
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/v1/videos/{videoId}",
     *     summary="Unassign a video",
     *     tags={"Videos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="videoId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Video unassigned successfully"),
     *     @OA\Response(response=404, description="Video not found")
     * )
     */
    public function updateStatusVideo($videoId)
    {
        // Temukan video berdasarkan ID
        $video = Video::findOrFail($videoId);

        // Update status dan hapus user_id
        $video->update([
            'status' => 'unassigned',
            'user_id' => null,
        ]);

        return response()->json([
            'message' => 'Video unassigned successfully',
            'data' => $video,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/v1/videos",
     *     summary="Get all videos",
     *     tags={"Videos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of all videos",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="raw_video_url", type="string"),
     *                 @OA\Property(property="processed_video_url", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index()
    {
        if (!auth()->user()->hasRole(['admin', 'doctor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $videos = Video::with('user')->get();

        $videos->transform(function ($video) {
            $video->raw_video_url = Storage::url($video->raw_video_path);
            $video->processed_video_url = Storage::url($video->processed_video_path);
            return $video;
        });

        return response()->json($videos);
    }

    /**
     * @OA\Get(
     *     path="/v1/videos/{videoId}",
     *     summary="Get video details by ID",
     *     tags={"Videos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="videoId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Video details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="raw_video_url", type="string"),
     *             @OA\Property(property="processed_video_url", type="string"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Video not found")
     * )
     */
    public function show()
    {
        $user = auth()->user();

        // Jika user memiliki role "patient", hanya tampilkan video yang di-assign ke dirinya
        if ($user->hasRole('pasien')) {
            $videos = Video::where('user_id', $user->id)->get();
        } else {
            // Admin dan dokter bisa melihat semua video
            $videos = Video::with('user')->get();
        }

        // Tambahkan URL lengkap untuk setiap video
        $videos->transform(function ($video) {
            $video->raw_video_url = Storage::url($video->raw_video_path);
            $video->processed_video_url = Storage::url($video->processed_video_path);
            return $video;
        });

        return response()->json($videos);
    }


    /**
     * Convert video to H.264 using FFmpeg
     */
    private function convertToH264($inputPath, $outputPath)
    {
        $command = "ffmpeg -i {$inputPath} -c:v libx264 -preset fast -crf 23 -c:a aac -b:a 128k {$outputPath} 2>&1";
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            \Log::error('FFmpeg conversion failed', ['output' => $output]);
        }
    }
}