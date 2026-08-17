<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkReport extends Model
{
    protected $fillable = [
        'officer_assignment_id', 'problem_identified', 'work_performed',
        'parts_used', 'completion_status', 'remarks', 'attended_at', 'reported_at',
    ];

    protected function casts(): array
    {
        return ['attended_at' => 'datetime', 'reported_at' => 'datetime'];
    }

    public function officerAssignment()
    {
        return $this->belongsTo(OfficerAssignment::class);
    }

    public function confirmation()
    {
        return $this->hasOne(DepartmentConfirmation::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
