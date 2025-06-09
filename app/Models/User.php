<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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
        'avatar_path',
        'password',
        'role',
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
        ];
    }

    public function createdTickets()
    {
        return $this->hasMany(Ticket::class, 'creator_id');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'creator_id');
    }

    public function offices()
    {
        return $this->belongsToMany(Office::class)->withTimestamps();
    }

    public function ticketReplies()
    {
        return $this->hasMany(TicketReply::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isAgent()
    {
        return $this->role === 'agent';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    public function hasRole($role)
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }

        return $this->role === $role;
    }

    /**
     * Get the user's avatar URL
     */
    public function getAvatarUrlAttribute(): string
    {
        // If user has uploaded avatar, use it
        if ($this->avatar_path && file_exists(storage_path('app/public/'.$this->avatar_path))) {
            return asset('storage/'.$this->avatar_path);
        }

        // Generate avatar using UI Avatars service
        $name = urlencode($this->name);
        $initials = $this->getInitials();

        // Create avatar with initials, background color based on role
        $background = $this->getAvatarBackgroundColor();
        $color = 'ffffff'; // White text

        return "https://ui-avatars.com/api/?name={$name}&initials={$initials}&background={$background}&color={$color}&size=128&font-size=0.5&rounded=true&bold=true";
    }

    /**
     * Get user initials for avatar
     */
    public function getInitials(): string
    {
        $nameParts = explode(' ', trim($this->name));

        if (count($nameParts) >= 2) {
            // First letter of first name + first letter of last name
            return strtoupper(substr($nameParts[0], 0, 1).substr(end($nameParts), 0, 1));
        } else {
            // First two letters of single name
            return strtoupper(substr($nameParts[0], 0, 2));
        }
    }

    /**
     * Get background color for avatar based on user role
     */
    public function getAvatarBackgroundColor(): string
    {
        return match ($this->role) {
            'admin' => 'dc2626',      // Red
            'agent' => '2563eb',      // Blue
            'customer' => '16a34a',   // Green
            default => '6b7280',      // Gray
        };
    }

    /**
     * Get avatar HTML img tag
     */
    public function getAvatarHtmlAttribute(): string
    {
        $url = $this->avatar_url;
        $name = htmlspecialchars($this->name);

        return "<img src=\"{$url}\" alt=\"{$name}\" class=\"w-8 h-8 rounded-full\" />";
    }

    /**
     * Get avatar URL for Filament
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }
}
