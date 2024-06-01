<?php

namespace Doefom\Restrict;

use Statamic;
use Statamic\Contracts\Auth\User;

class Restrict
{
    /**
     * Determine if the current user and route require restriction checks. Restriction checks
     * are only required for authenticated CP routes accessed by non-super users.
     */
    public static function shouldApplyRestriction(?User $user): bool
    {
        return Statamic::isCpRoute() && $user && ! $user->isSuper();
    }
}
