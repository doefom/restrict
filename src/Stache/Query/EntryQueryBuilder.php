<?php

namespace Doefom\Restrict\Stache\Query;

use Doefom\Restrict\Facades\Restrict;
use Statamic\Facades\User;
use Statamic\Stache\Query\EntryQueryBuilder as StatamicEntryQueryBuilder;

class EntryQueryBuilder extends StatamicEntryQueryBuilder
{

    protected function getFilteredKeys()
    {
        $resKeys = parent::getFilteredKeys();

        $user = User::current();

        if (! Restrict::isRestricted($user)) {
            return $resKeys;
        }

        // Note: At this point, we know there is an authenticated user that has restricted access.
        return $resKeys->filter(function ($key) use ($user) {
            $entry = $this->store->getItem($key);

            return Restrict::isAuthorized($user, $entry);
        });
    }

}
