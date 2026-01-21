<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function view(User $loggedInUser, User $targetUser): bool
    {
        if ($loggedInUser->isAdmin() || $loggedInUser->isManager()) {
            return $loggedInUser->organization_id === $targetUser->organization_id;
        }
        return $loggedInUser->id === $targetUser->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function update(User $loggedInUser, User $targetUser): bool
    {
        if ($loggedInUser->isAdmin() || $loggedInUser->isManager()) {
            return $loggedInUser->organization_id === $targetUser->organization_id;
        }
        return $loggedInUser->id === $targetUser->id;
    }

    public function delete(User $loggedInUser, User $targetUser): bool
    {
        return ($loggedInUser->isAdmin() || $loggedInUser->isManager())
            && $loggedInUser->organization_id === $targetUser->organization_id
            && $loggedInUser->id !== $targetUser->id;
    }
}
