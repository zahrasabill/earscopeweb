<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Penanganan",
 *     type="object",
 *     title="Penanganan",
 *     description="Penanganan model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="tanggal_penanganan", type="string", format="date", example="2024-01-15"),
 *     @OA\Property(property="keluhan", type="string", example="Telinga terasa sakit dan berdenging"),
 *     @OA\Property(property="riwayat_penyakit", type="string", example="Pernah mengalami infeksi telinga"),
 *     @OA\Property(property="diagnosis_manual", type="string", example="Otitis Media Akut"),
 *     @OA\Property(property="telinga_terkena", type="string", enum={"kiri", "kanan", "keduanya"}, example="kiri"),
 *     @OA\Property(property="tindakan", type="string", example="Pemberian antibiotik dan analgesik"),
 *     @OA\Property(property="status", type="string", enum={"unassigned", "assigned"}, example="unassigned"),
 *     @OA\Property(property="created_by", type="integer", example=2),
 *     @OA\Property(property="assigned_to", type="integer", nullable=true, example=3),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example="2024-01-16T10:30:00Z")
 * )
 */
class Penanganan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'tanggal_penanganan',
        'keluhan',
        'riwayat_penyakit',
        'diagnosis_manual',
        'telinga_terkena',
        'tindakan',
        'status',
        'created_by',
    ];

    protected $casts = [
        'tanggal_penanganan' => 'date',
    ];

    protected $dates = [
        'deleted_at',
    ];

    // Relasi ke User (Pasien)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke User yang membuat penanganan (Dokter/Admin)
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}