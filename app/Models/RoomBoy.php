<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomBoy extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function designations()
    {
        return $this->hasMany(Designation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
