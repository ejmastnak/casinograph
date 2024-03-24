<?php

namespace App\Policies;

use App\Models\PositionFamily;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PositionFamilyPolicy
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
     * Determine whether the user can view the model.
     */
    public function view(User $user, PositionFamily $positionFamily): bool
    {
        return is_null($positionFamily->user_id) || $positionFamily->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PositionFamily $positionFamily): bool
    {
        return $user->can_update && $positionFamily->user_id === $user->id;
    }
}
