<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $fillable = [
        'floor_id',
        'name',
        'is_active',
    ];

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class);
    }
}