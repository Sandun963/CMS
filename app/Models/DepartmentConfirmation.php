<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentConfirmation extends Model
{
    protected $fillable = [
        'work_report_id', 'confirmed_by', 'is_resolved', 'feedback', 'confirmed_at',
    ];

    protected function casts(): array
    {
        return ['confirmed_at' => 'datetime'];
    }

    public function workReport()
    {
        return $this->belongsTo(WorkReport::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
