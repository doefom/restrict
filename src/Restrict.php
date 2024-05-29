<?php

namespace Doefom\Restrict;

use Illuminate\Support\Facades\Route;
use Statamic\Contracts\Auth\User;

class Restrict
{
    /**
     * Determine if the current user and route require restriction checks. Restriction checks
     * are only required for authenticated CP routes accessed by non-super users.
     */
    public function shouldApplyRestriction(?User $user): bool
    {
        return $this->isAuthenticatedCpRoute() && $user && ! $user->isSuper();
    }

    /**
     * Check if the current route is a CP route that requires authentication by checking
     * if the 'statamic.cp.authenticated' middleware is applied to the route.
     */
    protected function isAuthenticatedCpRoute(): bool
    {
        return in_array('statamic.cp.authenticated', Route::current()->gatherMiddleware());
    }
}
