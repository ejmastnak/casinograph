<?php

namespace App\Policies;

use App\Models\FigureFamily;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FigureFamilyPolicy
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
    public function view(User $user, FigureFamily $figureFamily): bool
    {
        return is_null($figureFamily->user_id) || $figureFamily->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FigureFamily $figureFamily): bool
    {
        return $user->can_crud && $figureFamily->user_id === $user->id;
    }
}
