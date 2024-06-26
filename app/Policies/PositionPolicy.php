<?php

namespace App\Policies;

use App\Models\Position;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PositionPolicy
{

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->is_admin) return true;
        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Position $position): bool
    {
        return is_null($position->user_id) || $position->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can_crud;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Position $position): bool
    {
        return $user->can_crud && $position->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Position $position): bool
    {
        return $user->can_crud && $position->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Position $position): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Position $position): bool
    {
        //
    }
}
