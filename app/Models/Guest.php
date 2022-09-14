<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function damages()
    {
        return $this->hasMany(Damage::class);
    }
}
