<?php

namespace App\View\Components;

use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UserAvatar extends Component
{
    public User $user;

    public string $size;

    public string $class;

    /**
     * Create a new component instance.
     */
    public function __construct(User $user, string $size = 'md', string $class = '')
    {
        $this->user = $user;
        $this->size = $size;
        $this->class = $class;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.user-avatar');
    }

    /**
     * Get size classes based on size parameter
     */
    public function getSizeClasses(): string
    {
        return match ($this->size) {
            'xs' => 'w-6 h-6',
            'sm' => 'w-8 h-8',
            'md' => 'w-10 h-10',
            'lg' => 'w-12 h-12',
            'xl' => 'w-16 h-16',
            '2xl' => 'w-20 h-20',
            default => 'w-10 h-10',
        };
    }

    /**
     * Get avatar URL with appropriate size
     */
    public function getAvatarUrl(): string
    {
        $pixelSize = match ($this->size) {
            'xs' => 24,
            'sm' => 32,
            'md' => 40,
            'lg' => 48,
            'xl' => 64,
            '2xl' => 80,
            default => 40,
        };

        // If user has uploaded avatar, use it
        if ($this->user->avatar_path && file_exists(storage_path('app/public/'.$this->user->avatar_path))) {
            return asset('storage/'.$this->user->avatar_path);
        }

        // Generate avatar using UI Avatars service
        $name = urlencode($this->user->name);
        $initials = $this->user->getInitials();
        $background = $this->user->getAvatarBackgroundColor();

        return "https://ui-avatars.com/api/?name={$name}&initials={$initials}&background={$background}&color=ffffff&size={$pixelSize}&font-size=0.5&rounded=true&bold=true";
    }
}
