<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
    { 
        
    protected $fillable = [
        'attachable_id',
        'attachable_type',
        'uploaded_by',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    public function attachable()
    {
        return $this->morphTo();
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function url(): string
    {
        return asset('storage/' . $this->path);
    }
}
