<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PassportVerifier extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'state',
        'code_verifier'
    ];
}
