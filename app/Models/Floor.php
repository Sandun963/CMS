<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{

    protected $fillable = [
        'name',
        'is_active',
    ];

    public function divisions()
    {
        return $this->hasMany(
            Division::class,
            'floor_id',
            'id'
        );
    }
}