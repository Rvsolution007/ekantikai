<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class SuperAdmin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'super_admins';
    protected $guard = 'admin';

    protected $fillable = [
        'admin_id',
        'name',
        'email',
        'password',
        'role',
        'is_super_admin',
        'is_active',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    // Role constants
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';

    /**
     * Check if admin is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Check if admin is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get assigned leads
     */
    public function assignedLeads()
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    /**
     * Get avatar URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff';
    }
}
