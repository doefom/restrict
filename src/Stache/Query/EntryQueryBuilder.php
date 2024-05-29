<?php

namespace Doefom\Restrict\Stache\Query;

use Doefom\Restrict\Facades\Restrict;
use Statamic\Facades\User;
use Statamic\Policies\EntryPolicy;
use Statamic\Stache\Query\EntryQueryBuilder as StatamicEntryQueryBuilder;

class EntryQueryBuilder extends StatamicEntryQueryBuilder
{
    protected function getFilteredKeys()
    {
        $resKeys = parent::getFilteredKeys();

        $user = User::current();

        if (! Restrict::shouldApplyRestriction($user)) {
            return $resKeys;
        }

        $entryPolicy = app(EntryPolicy::class);

        return $resKeys->filter(function ($key) use ($user, $entryPolicy) {
            $entry = $this->store->getItem($key);

            return $entryPolicy->view($user, $entry);
        });
    }
}
