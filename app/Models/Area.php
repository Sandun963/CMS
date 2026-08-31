<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = [
        'division_id',
        'name',
        'is_active',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}