<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Defense extends Model
{
    use HasFactory;

    protected $fillable = [
        'student',
        'promoter',
        'examiner',
    ];
}
