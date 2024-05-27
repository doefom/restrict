<?php

namespace Doefom\Restrict;

use Closure;
use Illuminate\Support\Facades\Route;
use Statamic\Contracts\Auth\User;
use Statamic\Contracts\Entries\Entry;

class Restrict
{
    protected Closure $callback;

    public function __construct()
    {
        // Default callback returns true which will ultimately not apply any
        // restrictions and therefore preserve the default behavior.
        $this->callback = fn (User $user, Entry $entry) => true;
    }

    /**
     * Set the restriction callback used to determine if the current user is authorized to view the entry.
     * The callback expects a User and Entry instance and should return true when the user is authorized
     * to view the entry and false otherwise.
     */
    public function setRestriction(callable $callback): void
    {
        $this->callback = $callback;
    }

    /**
     * Check if the current user is authorized to view the entry.
     */
    public function isAuthorized(User $user, Entry $entry): bool
    {
        return call_user_func($this->callback, $user, $entry);
    }

    /**
     * Determine if the current user and route require restriction checks. Restriction
     * checks are only required for authenticated CP routes and non-super users.
     */
    public function isRestricted(?User $user): bool
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
