<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $request->validate([
            'raw_video' => 'required|file|mimetypes:video/mp4,video/quicktime',
            'processed_video' => 'required|file|mimetypes:video/mp4,video/quicktime',
        ]);

        $timestamp = now()->format('Ymd_His');
        $folderPath = "videos/{$timestamp}";

        // Simpan video mentah
        $rawVideoFilename = "raw_{$timestamp}.mp4";
        $rawVideoPath = $request->file('raw_video')->storeAs($folderPath, $rawVideoFilename, 'private');

        // Simpan video dengan bounding box
        $processedVideoFilename = "bb_{$timestamp}.mp4";
        $processedVideoPath = $request->file('processed_video')->storeAs($folderPath, $processedVideoFilename, 'private');

        // Konversi ke H.264
        $convertedRawVideoFilename = "raw_{$timestamp}_h264.mp4";
        $convertedRawVideoPath = "{$folderPath}/{$convertedRawVideoFilename}";

        $convertedProcessedVideoFilename = "bb_{$timestamp}_h264.mp4";
        $convertedProcessedVideoPath = "{$folderPath}/{$convertedProcessedVideoFilename}";

        // Jalankan FFmpeg untuk konversi
        $this->convertToH264(storage_path("app/private/{$rawVideoPath}"), storage_path("app/private/{$convertedRawVideoPath}"));
        $this->convertToH264(storage_path("app/private/{$processedVideoPath}"), storage_path("app/private/{$convertedProcessedVideoPath}"));

        // Simpan data ke database
        $video = Video::create([
            'raw_video_path' => $convertedRawVideoPath,
            'processed_video_path' => $convertedProcessedVideoPath,
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
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function showAllVideos()
    {
        // Ambil semua video beserta relasi user
        $videos = Video::with('user')->get();

        // Tambahkan URL lengkap untuk setiap video
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
     *     @OA\Response(response=404, description="Video not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function showById($videoId)
    {
        // Cari video berdasarkan ID
        $video = Video::with('user')->findOrFail($videoId);

        // Tambahkan URL lengkap untuk video
        $video->raw_video_url = Storage::url($video->raw_video_path);
        $video->processed_video_url = Storage::url($video->processed_video_path);

        return response()->json($video);
    }

    /**
     * @OA\Get(
     *     path="/v1/videos/pasien",
     *     summary="Get videos assigned to the logged-in patient",
     *     tags={"Videos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of videos assigned to the patient",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="raw_video_url", type="string"),
     *                 @OA\Property(property="processed_video_url", type="string"),
     *                 @OA\Property(property="status", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function showVideosByPasien()
    {
        $user = auth()->user();

        // Pastikan hanya user dengan role "pasien" yang bisa mengakses endpoint ini
        if (!$user->hasRole('pasien')) {
            return response()->json(['message' => 'Forbidden: Only patients can access this endpoint'], 403);
        }

        // Ambil video yang di-assign ke pasien ini (berdasarkan user_id)
        $videos = Video::where('user_id', $user->id)->get();

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

        // Jika berhasil, simpan file ke storage Laravel
        if (file_exists($outputPath)) {
            Storage::disk('private')->put($outputPath, file_get_contents($outputPath));
            unlink($outputPath); // Hapus file sementara dari local
        }
    }


    /**
     * @OA\Get(
     *     path="/v1/videos/stream/{filename}",
     *     summary="Stream a video file",
     *     tags={"Videos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="filename",
     *         in="path",
     *         required=true,
     *         description="The name of the video file to stream",
     *         @OA\Schema(type="string")
     *     ),
     *@OA\Response(
     *response=200,
     *description="Video streaming successfully",
     *@OA\Header(
     *    header="Content-Type",
     *    description="MIME type of the video",
     *    @OA\Schema(type="string", example="video/mp4")
     *),
     *@OA\Header(
     *   header="Accept-Ranges",
     *   description="bytes range support",
     *   @OA\Schema(type="string", example="bytes")
     *)
     *),
     *     @OA\Response(
     *         response=404,
     *         description="Video not found"
     *     )
     * )
     */

    public function streamVideo($filename)
    {
        // Cari video berdasarkan nama file di database
        $video = Video::where('raw_video_path', 'like', "%{$filename}")
            ->orWhere('processed_video_path', 'like', "%{$filename}")
            ->first();

        // Jika video tidak ditemukan dalam database
        if (!$video) {
            return response()->json(['message' => 'Video not found in database'], 404);
        }

        // Pastikan file benar-benar ada di penyimpanan private
        $filePath = null;
        if (Storage::disk('private')->exists($video->raw_video_path)) {
            $filePath = $video->raw_video_path;
        } elseif (Storage::disk('private')->exists($video->processed_video_path)) {
            $filePath = $video->processed_video_path;
        }

        // Jika file tidak ditemukan di storage
        if (!$filePath) {
            return response()->json(['message' => 'Video file not found'], 404);
        }

        // Stream video
        return new StreamedResponse(function () use ($filePath) {
            $stream = Storage::disk('private')->readStream($filePath);
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => 'video/mp4',
            'Accept-Ranges' => 'bytes',
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
        ]);
    }


}