<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'username', 'password', 'role_id',
        'department_id', 'phone', 'specialty', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role?->code === 'sup_admin';
    }

    public function isItHead(): bool
    {
        return $this->role?->code === Role::IT_HEAD;
    }

    public function isAssignOfficer(): bool
    {
        return $this->role?->code === Role::ASSIGN_OFFICER;
    }

    public function isTechnicalOfficer(): bool
    {
        return $this->role?->code === Role::TECHNICAL_OFFICER;
    }

    public function isMinistryUser(): bool
    {
        return $this->role?->code === Role::MINISTRY_USER;
    }

    public function requestsSubmitted()
    {
        return $this->hasMany(BreakdownRequest::class, 'requested_by');
    }

    public function requestsAssignedToMe()
    {
        return $this->hasMany(BreakdownRequest::class, 'assigned_to');
    }

    public function assignmentsAsOfficer()
    {
        return $this->hasMany(Assignment::class, 'assign_officer_id');
    }

    public function officerAssignmentsAsTechnician()
    {
        return $this->hasMany(OfficerAssignment::class, 'technical_officer_id');
    }
}
