<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Defense extends Model {
    use HasFactory;

    protected $fillable = [
        'examinerID',
        'egzaminer_name',
        'examiner2ID',
        'egzaminer2_name',
        'promoterID',
        'promoter_name',
        'student',
        'CalendarID',
        'EgzamDate'
    ];

    public function examiner() {
        return $this->belongsTo(Teacher::class, 'examinerID', 'Teacher-ID');
    }

    public function examiner2() {
        return $this->belongsTo(Teacher::class, 'examiner2ID', 'Teacher-ID');
    }

    public function promoter() {
        return $this->belongsTo(Teacher::class, 'promoterID', 'Teacher-ID');
    }

    public function calendar() {
        return $this->belongsTo(Calendar::class, 'CalendarID', 'id');
    }
}
