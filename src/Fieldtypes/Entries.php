<?php

namespace Doefom\Restrict\Fieldtypes;

use Statamic\Facades\User;
use Statamic\Support\Arr;

class Entries extends \Statamic\Fieldtypes\Entries
{

//    public function getIndexQuery($request)
//    {
//        $user = User::current();
//        $query = parent::getIndexQuery($request);
//
//        if (!$user || $user->isSuper()) {
//            return $query;
//        }
//
//        $collectionHandles = $this->getConfiguredCollections();
//
//
//        foreach ($collectionHandles as $index => $collectionHandle) {
//            if ($user->hasPermission("view other author's {$collectionHandle} entries")) {
//                continue;
//            }
//
//            $query->orWhere(function ($query) use ($collectionHandle, $user) {
//                $query
//                    ->where('collection', $collectionHandle)
//                    ->where('author', $user->id);
//            });
//
//        }
//
//        return $query;
//    }

    public function getIndexItems($request)
    {
        $query = $this->getIndexQuery($request);

        $filters = $request->filters;

        if (! isset($filters['collection'])) {
            $query->whereIn('collection', $this->getConfiguredCollections());
        }

        if ($blueprints = $this->config('blueprints')) {
            $query->whereIn('blueprint', $blueprints);
        }

        $this->activeFilterBadges = $this->queryFilters($query, $filters, $this->getSelectionFilterContext());

        // TODO

        if ($sort = $this->getSortColumn($request)) {
            $query->orderBy($sort, $this->getSortDirection($request));
        }

        return $request->boolean('paginate', true) ? $query->paginate() : $query->get();
    }

}
