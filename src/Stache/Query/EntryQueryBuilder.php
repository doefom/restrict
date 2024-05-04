<?php

namespace Doefom\Restrict\Stache\Query;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Statamic\Entries\Entry;
use Statamic\Facades\User;
use Statamic\Stache\Query\EntryQueryBuilder as StatamicEntryQueryBuilder;
use Statamic\Support\Str;

class EntryQueryBuilder extends StatamicEntryQueryBuilder
{

    protected function getFilteredKeys()
    {
        $resKeys = parent::getFilteredKeys();

        // Only apply the restriction if:
        // 1. The current route is a CP route that requires authentication
        // 2. The current user is not a super user
        if (!$this->isAuthenticatedCpRoute() || User::current()?->isSuper()) {
            return $resKeys;
        }


        // Note: At this point, we know there is an authenticated user.
        return $resKeys->filter(function ($key) {
            $entry = $this->store->getItem($key);

            return $this->isAuthorized($entry);
        });
    }

    /**
     * Check if the current user is authorized to view the entry. This is done by checking if the user has permission to
     * view other authors' entries in the entry's collection or if the user is the author of the entry.
     *
     * @param Entry $entry
     * @return bool
     */
    protected function isAuthorized(Entry $entry): bool
    {
        // TODO: Check the entry blueprint, not each entry's content

        $author = $entry->author;

        // If the entry has no author, the addon has no effect.
        if (!$author) {
            return true;
        }

        $authorizedCollections = $this->getAllAuthorizedCollections();

        $isInAuthorizedCollections = in_array($entry->collectionHandle(), $authorizedCollections);
        $isAuthor = $author->id() === User::current()->id();

        return $isInAuthorizedCollections || $isAuthor;
    }

    /**
     * Get all collections that the current user is authorized to view other authors' entries in.
     *
     * @return array
     */
    private function getAllAuthorizedCollections(): array
    {
        return User::current()->permissions()
            ->filter(fn($permission) => Str::contains($permission, "view other authors'"))
            ->map(function ($permission) {
                return Str::between($permission, "view other authors' ", " entries");
            })
            ->values()
            ->toArray();
    }

    /**
     * Check if the current route is a CP route that requires authentication by checking if the
     * 'statamic.cp.authenticated' middleware is applied to the route.
     *
     * @return bool
     */
    private function isAuthenticatedCpRoute(): bool
    {
        return in_array('statamic.cp.authenticated', Route::current()->gatherMiddleware());
    }

    private function hasAnotherAuthor($user, $entry)
    {
        if ($entry->blueprint()->hasField('author') === false) {
            return false;
        }

        return ! $entry->authors()->contains($user->id());
    }

}
