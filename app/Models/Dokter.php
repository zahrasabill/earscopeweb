<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Dokter extends User
{
    use HasRoles;

    protected $table = 'users';
}
