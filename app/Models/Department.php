<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['ministry_name', 'name', 'code', 'floor', 'location', 'phone', 'is_active'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function breakdownRequests()
    {
        return $this->hasMany(BreakdownRequest::class);
    }
}
