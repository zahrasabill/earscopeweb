<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class Pasien extends User
{
    use HasRoles;
    
    protected $table = 'users';

    public function examinations(): HasMany
    {
        return $this->hasMany(Examination::class, 'pasien_id', 'id');
    }
}
