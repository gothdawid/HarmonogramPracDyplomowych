<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;
    protected $fillable = [
        'Departament-ID',
        'Teacher-ID',
        'Teacher-Name',
        'Jednostka',
        'Jednostka-en',
        'Plan-ID',
        'DAY',
        'OD_GODZ',
        'DO_GODZ',
        'G_OD',
        'G_DO',
        'NAME',
        'NAME_EN',
        'ID_KALENDARZ',
        'TERMIN_K'
    ];
    
    public function department() {
        return $this->belongsTo(Department::class, 'Departament-ID');
    }
}
