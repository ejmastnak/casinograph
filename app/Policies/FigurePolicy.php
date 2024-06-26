<?php

namespace App\Policies;

use App\Models\Figure;
use App\Models\Position;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FigurePolicy
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
    public function view(User $user, Figure $figure): bool
    {
        return is_null($figure->user_id) || $figure->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can_crud;
    }

    public function createFromPosition(User $user, Position $position): bool
    {
        return $user->can_crud;
    }

    public function createToPosition(User $user, Position $position): bool
    {
        return $user->can_crud;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Figure $figure): bool
    {
        return $user->can_crud && $figure->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Figure $figure): bool
    {
        return $user->can_crud && $figure->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Figure $figure): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Figure $figure): bool
    {
        //
    }
}
