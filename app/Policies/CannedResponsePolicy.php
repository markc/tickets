<?php

namespace App\Policies;

use App\Models\CannedResponse;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CannedResponsePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAgent() || $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CannedResponse $cannedResponse): bool
    {
        if ($user->isCustomer()) {
            return false;
        }

        // Admins can view any response, users can view public responses or their own responses
        return $user->isAdmin() || $cannedResponse->is_public || $cannedResponse->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAgent() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CannedResponse $cannedResponse): bool
    {
        if ($user->isCustomer()) {
            return false;
        }

        // Admins can edit any response, agents can only edit their own
        return $user->isAdmin() || $cannedResponse->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CannedResponse $cannedResponse): bool
    {
        if ($user->isCustomer()) {
            return false;
        }

        // Admins can delete any response, agents can only delete their own
        return $user->isAdmin() || $cannedResponse->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CannedResponse $cannedResponse): bool
    {
        return $this->update($user, $cannedResponse);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CannedResponse $cannedResponse): bool
    {
        return $this->delete($user, $cannedResponse);
    }
}
