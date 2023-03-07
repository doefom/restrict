<?php

namespace Doefom\Restrict\Fieldtypes;

use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Support\Arr;

class Entries extends \Statamic\Fieldtypes\Entries
{

    public function getIndexQuery($request)
    {

        $query = parent::getIndexQuery($request);

        foreach ($this->getConfiguredCollections() as $collectionHandle) {
            // If the user has permission to view other authors' entries for the given collection, do nothing.
            if (User::current()->isSuper() || User::current()->hasPermission("view other authors' $collectionHandle entries")) {
                continue;
            }
            // Else, get all entries of the given collection that do not belong to the current user.
            $entriesOfOtherUsers = Entry::query()
                ->where('collection', $collectionHandle)
                ->whereNotIn('author', [User::current()->id])
                ->get();
            // Exclude those entries from the query.
            $query->whereNotIn('id', $entriesOfOtherUsers->pluck('id')->toArray());
        }

        return $query;
    }

}
