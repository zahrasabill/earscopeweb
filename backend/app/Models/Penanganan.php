<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
 *     @OA\Property(property="created_by", type="integer", example=2),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="John Doe"),
 *         @OA\Property(property="email", type="string", example="john@example.com"),
 *         @OA\Property(property="role", type="string", example="pasien")
 *     ),
 *     @OA\Property(
 *         property="created_by_user",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=2),
 *         @OA\Property(property="name", type="string", example="Dr. Smith"),
 *         @OA\Property(property="email", type="string", example="doctor@example.com"),
 *         @OA\Property(property="role", type="string", example="dokter")
 *     )
 * )
 */
class Penanganan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'penanganans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'tanggal_penanganan',
        'keluhan',
        'riwayat_penyakit',
        'diagnosis_manual',
        'telinga_terkena',
        'tindakan',
        'created_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_penanganan' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the penanganan (patient).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who created this penanganan record (doctor/admin).
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include penanganan for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include penanganan created by a specific user.
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope a query to filter by telinga terkena.
     */
    public function scopeByTelinga($query, $telinga)
    {
        return $query->where('telinga_terkena', $telinga);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_penanganan', [$startDate, $endDate]);
    }

    /**
     * Get formatted tanggal penanganan.
     */
    public function getFormattedTanggalAttribute()
    {
        return $this->tanggal_penanganan->format('d F Y');
    }

    /**
     * Get the patient name.
     */
    public function getPatientNameAttribute()
    {
        return $this->user->name ?? 'Unknown';
    }

    /**
     * Get the doctor name who created this record.
     */
    public function getDoctorNameAttribute()
    {
        return $this->createdByUser->name ?? 'System';
    }
}