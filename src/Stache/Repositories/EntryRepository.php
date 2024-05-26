<?php

namespace Doefom\Restrict\Stache\Repositories;

use Doefom\Restrict\Stache\Query\EntryQueryBuilder;
use Statamic\Contracts\Entries\QueryBuilder;
use Statamic\Stache\Repositories\EntryRepository as StatamicEntryRepository;

class EntryRepository extends StatamicEntryRepository
{
    public function query(): QueryBuilder
    {
        return new EntryQueryBuilder($this->store);
    }
}
