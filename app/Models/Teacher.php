<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model {
    use HasFactory;

    protected $fillable = [
        'Teacher-ID',
        'Teacher-Name',
    ];

    public function lessons() {
        return $this->hasMany(Lesson::class, "Teacher-ID", "Teacher-ID");
    }
}
