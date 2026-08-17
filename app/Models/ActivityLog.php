<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['request_id', 'user_id', 'action', 'details'];

    public function request()
    {
        return $this->belongsTo(BreakdownRequest::class, 'request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log(?int $requestId, ?int $userId, string $action, ?string $details = null): void
    {
        static::create([
            'request_id' => $requestId,
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
        ]);
    }
}
