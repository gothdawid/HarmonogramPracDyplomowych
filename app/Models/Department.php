<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'Departament-ID',
        'Departament-Name',
        'size'
    ];

    public function lessons() {
        return $this->hasMany(Lesson::class, 'Departament-ID');
    }
}