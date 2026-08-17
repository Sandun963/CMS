<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    const IT_HEAD = 'it_head';
    const ASSIGN_OFFICER = 'assign_officer';
    const TECHNICAL_OFFICER = 'technical_officer';
    const MINISTRY_USER = 'ministry_user';

    protected $fillable = ['name', 'code'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
