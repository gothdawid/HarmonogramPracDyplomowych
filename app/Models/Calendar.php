<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendar extends Model {
    use HasFactory;

    protected $fillable = [
        'OwnerID',
        'Calendar_Name'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'OwnerID', 'id');
    }

    public function defenses() {
        return $this->hasMany(Defense::class, 'CalendarID', 'id');
    }
}
