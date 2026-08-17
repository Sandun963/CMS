<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'request_id', 'assigned_by', 'assign_officer_id', 'note', 'assigned_at', 'status',
    ];

    protected function casts(): array
    {
        return ['assigned_at' => 'datetime'];
    }

    public function request()
    {
        return $this->belongsTo(BreakdownRequest::class, 'request_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignOfficer()
    {
        return $this->belongsTo(User::class, 'assign_officer_id');
    }

    public function officerAssignments()
    {
        return $this->hasMany(OfficerAssignment::class);
    }

    public function latestOfficerAssignment()
    {
        return $this->hasOne(OfficerAssignment::class)->latestOfMany();
    }
}
