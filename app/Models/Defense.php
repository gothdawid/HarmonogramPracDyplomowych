<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Defense extends Model
{
    use HasFactory;

    protected $fillable = [
        'examiner',
        'examiner2',
        'promoter',
        'student',
        'CalendarID',
        'EgzamDate'
    ];

    public function examiner()
    {
        return $this->belongsTo(Teacher::class, 'examiner', 'Teacher-ID');
    }

    public function examiner2()
    {
        return $this->belongsTo(Teacher::class, 'examiner2', 'Teacher-ID');
    }

    public function promoter()
    {
        return $this->belongsTo(Teacher::class, 'promoter', 'Teacher-ID');
    }

    public function calendar()
    {
        return $this->belongsTo(Calendar::class, 'CalendarID', 'id');
    }
}