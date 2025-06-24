<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penanganan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'status',
        'tanggal_penanganan',
        'keluhan',
        'riwayat_penyakit',
        'diagnosis_manual',
        'telinga_terkena',
        'tindakan',
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
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Relasi ke User yang membuat penanganan (Dokter/Admin)
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}