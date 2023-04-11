<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'time',
        'OwnerID',
        'DefenseID',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'OwnerID', 'id');
    }

    public function defense() {
        return $this->belongsTo(Defense::class, 'DefenseID', 'id');
    }
}