<?php

namespace App\Policies;

use App\Models\CompoundFigure;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CompoundFigurePolicy
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
    public function view(User $user, CompoundFigure $compound_figure): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can_create;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CompoundFigure $compound_figure): bool
    {
        return $user->can_update && $compound_figure->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CompoundFigure $compound_figure): bool
    {
        return $user->can_delete && $compound_figure->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CompoundFigure $compound_figure): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CompoundFigure $compound_figure): bool
    {
        //
    }
}
