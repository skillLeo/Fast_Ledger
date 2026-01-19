<?php

namespace App\Policies;

use App\Models\HmrcObligation;
use App\Models\User;

class HmrcObligationPolicy
{
    /**
     * Determine if the user can view any obligations.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the obligation.
     */
    public function view(User $user, HmrcObligation $obligation): bool
    {
        return $user->User_ID === $obligation->user_id;
    }

    /**
     * Determine if the user can sync obligations.
     */
    public function sync(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can export obligations.
     */
    public function export(User $user): bool
    {
        return true;
    }
}

