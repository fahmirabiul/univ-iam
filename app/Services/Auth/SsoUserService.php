<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;

class SsoUserService
{
    /**
     * Retrieve the authenticated user with eager-loaded profile and roles.
     */
    public function getAuthenticatedUserProfile(User $user): User
    {
        return $user->loadMissing(['profile', 'roles']);
    }
}
