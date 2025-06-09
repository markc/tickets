<?php

namespace App\Filament\AvatarProviders;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class CustomAvatarProvider implements AvatarProvider
{
    public function get(Authenticatable $user): string
    {
        return $user->avatar_url;
    }
}
