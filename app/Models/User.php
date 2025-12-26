<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'is_banned',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
        ];
    }

    /**
     * Get user's bookings
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get user's confirmed bookings
     */
    public function confirmedBookings(): HasMany
    {
        return $this->hasMany(Booking::class)->where('status', 'confirmed');
    }

    /**
     * Get created events
     */
    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    /**
     * Get avatar URL
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) {
            return null;
        }

        if (Str::startsWith($this->avatar, ['http://', 'https://'])) {
            return $this->avatar;
        }

        return asset('storage/' . $this->avatar);
    }

    /**
     * Check if user can access Filament panel
     * 
     * Admin panel access requires:
     * - User role is 'admin'
     * - User is not banned
     * - For production: Email should be verified (relaxed for development)
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Only admin role can access admin panel
        if ($this->role !== 'admin') {
            return false;
        }

        // Banned users cannot access admin panel
        if ($this->is_banned) {
            return false;
        }

        // In production, require email verification
        // In development, allow access without verification
        if (app()->environment('production')) {
            return $this->email_verified_at !== null;
        }

        return true;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' && !$this->is_banned;
    }
    
    /**
     * Check if user is regular user
     */
    public function isUser(): bool
    {
        return $this->role === 'user' && !$this->is_banned;
    }
}
