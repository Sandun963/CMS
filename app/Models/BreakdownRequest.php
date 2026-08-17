<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakdownRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number', 'department_id', 'requested_by', 'category_id',
        'title', 'description', 'location', 'status',
        'assigned_to', 'received_at',
    ];

    protected function casts(): array
    {
        return ['received_at' => 'datetime'];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'request_id');
    }

    public function latestAssignment()
    {
        return $this->hasOne(Assignment::class, 'request_id')->latestOfMany();
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'request_id')->latest();
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'New' => 'bg-primary',
            'Assigned' => 'bg-warning text-dark',
            'In Progress' => 'bg-info text-dark',
            'Resolved' => 'bg-success',
            'Closed' => 'bg-secondary',
            'Reopened' => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
