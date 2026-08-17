<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficerAssignment extends Model
{
    protected $fillable = [
        'assignment_id', 'technical_officer_id', 'assigned_by',
        'due_date', 'note', 'assigned_at', 'status',
    ];

    protected function casts(): array
    {
        return ['assigned_at' => 'datetime', 'due_date' => 'date'];
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function technicalOfficer()
    {
        return $this->belongsTo(User::class, 'technical_officer_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function workReport()
    {
        return $this->hasOne(WorkReport::class);
    }
}
