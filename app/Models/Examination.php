<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Examination extends Model
{
    protected $table = 'hasil_pemeriksaan';

    protected $fillable = [
        'pasien_id', 
        'video_raw_path', 
        'video_ai_path', 
        'diagnosa', 
        'examined_at'
    ];

    protected $casts = [
        'examined_at' => 'datetime',
    ];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'pasien_id', 'id');
    }
}