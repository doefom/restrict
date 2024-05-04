<?php

namespace Doefom\Restrict\Stache\Query;

use Illuminate\Support\Facades\Route;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Entries\Entry;
use Statamic\Facades\User;
use Statamic\Stache\Query\EntryQueryBuilder as StatamicEntryQueryBuilder;
use Statamic\Support\Str;

class EntryQueryBuilder extends StatamicEntryQueryBuilder
{
    protected function getFilteredKeys()
    {
        $resKeys = parent::getFilteredKeys();

        $user = User::current();

        if ($user === null) {
            return $resKeys;
        }

        // Only apply the restriction if:
        // 1. The current route is a CP route that requires authentication
        // 2. The current user is not a super user
        if (! $this->isAuthenticatedCpRoute() || $user->isSuper()) {
            return $resKeys;
        }

        // Note: At this point, we know there is an authenticated user.
        return $resKeys->filter(function ($key) use ($user) {
            $entry = $this->store->getItem($key);

            return $this->isAuthorized($user, $entry);
        });
    }

    /**
     * Check if the current user is authorized to view the entry. This is done by checking if the user has permission to
     * view other authors' entries in the entry's collection or if the user is the author of the entry.
     */
    protected function isAuthorized(UserContract $user, Entry $entry): bool
    {
        if ($this->hasAnotherAuthor($user, $entry)) {
            return $this->isInAuthorizedCollections($user, $entry);
        }

        return true;
    }

    protected function isInAuthorizedCollections(UserContract $user, Entry $entry): bool
    {
        // Get all collections where the user has permission to view other authors' entries
        $authorizedCollections = $user->permissions()
            ->filter(fn ($permission) => Str::contains($permission, "view other authors'"))
            ->map(function ($permission) {
                return Str::between($permission, "view other authors' ", ' entries');
            })
            ->values()
            ->toArray();

        // Check if the entry's collection is in the list of authorized collections
        return in_array($entry->collectionHandle(), $authorizedCollections);
    }

    /**
     * Check if the current route is a CP route that requires authentication by checking if the
     * 'statamic.cp.authenticated' middleware is applied to the route.
     */
    protected function isAuthenticatedCpRoute(): bool
    {
        return in_array('statamic.cp.authenticated', Route::current()->gatherMiddleware());
    }

    protected function hasAnotherAuthor(UserContract $user, $entry): bool
    {
        if ($entry->blueprint()->hasField('author') === false) {
            return false;
        }

        return ! $entry->authors()->contains($user->id());
    }
}
