<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $primaryKey = 'division_id';

    protected $fillable = [
        'floor_id',
        'name',
        'is_active',
    ];

    public function floor()
    {
        return $this->belongsTo(
            Floor::class,
            'floor_id',
            'floor_id'
        );
    }

    public function areas()
    {
        return $this->hasMany(
            Area::class,
            'division_id',
            'division_id'
        );
    }
}