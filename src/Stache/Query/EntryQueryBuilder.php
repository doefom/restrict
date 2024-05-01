<?php

namespace Doefom\Restrict\Stache\Query;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Statamic\Entries\EntryCollection;
use Statamic\Facades\User;
use Statamic\Stache\Query\EntryQueryBuilder as StatamicEntryQueryBuilder;
use Statamic\Support\Str;

class EntryQueryBuilder extends StatamicEntryQueryBuilder
{

    protected function getFilteredKeys()
    {
        $collections = empty($this->collections)
            ? \Statamic\Facades\Collection::handles()
            : $this->collections;

        $this->addTaxonomyWheres();

        $unauthorizedCollections = $this->filterUnauthorizedCollections($collections);

        if (empty($unauthorizedCollections)) {
            return empty($this->wheres)
                ? $this->getKeysFromCollections($collections)
                : $this->getKeysFromCollectionsWithWheres($collections, $this->wheres);
        } else {
            $authorizedCollections = $this->filterAuthorizedCollections($collections);

            $resAuthorized = empty($this->wheres)
                ? $this->getKeysFromCollections($authorizedCollections)
                : $this->getKeysFromCollectionsWithWheres($authorizedCollections, $this->wheres);

            $wheres = $this->wheres;
            $wheres[] = $this->getAuthorWhere();

            $resUnauthorized = $this->getKeysFromCollectionsWithWheres($unauthorizedCollections, $wheres);

            return $resAuthorized->merge($resUnauthorized);
        }

    }

    private function getAuthorWhere(): array
    {
        $user = User::current();

        return [
            'type' => 'Basic',
            'column' => 'author',
            'value' => $user->id(),
            'operator' => '=',
            'boolean' => 'and',
        ];
    }

    private function filterUnauthorizedCollections(array $collections): array
    {
        if (Auth::user()?->isSuper()) return [];

        return collect($collections)
            ->filter(fn($collection) => !$this->isAuthorizedCollection($collection))
            ->values()
            ->toArray();
    }

    private function filterAuthorizedCollections(array $collections): array
    {
        if (Auth::user()?->isSuper()) return $collections;

        return collect($collections)
            ->filter(fn($collection) => $this->isAuthorizedCollection($collection))
            ->values()
            ->toArray();
    }

    private function isAuthorizedCollection(string $collection): bool
    {
        return in_array($collection, $this->getAllAuthorizedCollections());
    }

    private function getAllAuthorizedCollections(): array
    {
        $user = User::current();

        return $user->permissions()
            ->filter(fn($permission) => Str::contains($permission, "view other authors'"))
            ->map(function ($permission) {
                return Str::between($permission, "view other authors' ", " entries");
            })
            ->values()
            ->toArray();
    }

}
