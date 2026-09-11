<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Escalation extends Model
{
    protected $fillable = [
        'breakdown_request_id',
        'forwarded_by',
        'trigger_type',
        'reason',
        'status',
        'admin_decision',
        'admin_remarks',
        'reviewed_by',
        'forwarded_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'forwarded_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function breakdownRequest()
    {
        return $this->belongsTo(
            BreakdownRequest::class
        );
    }

    public function forwardedBy()
    {
        return $this->belongsTo(
            User::class,
            'forwarded_by'
        );
    }

    public function reviewedBy()
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }
}