<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function foodCategory(){
        return $this->belongsTo(FoodCategory::class);
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
