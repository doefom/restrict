<?php

namespace Doefom\Restrict\Stache\Repositories;

use Statamic\Facades\User;

class EntryRepository extends \Statamic\Stache\Repositories\EntryRepository
{

    public function query()
    {
        $user = User::current();

        if (!$user || $user->isSuper() || $user->hasPermission("view other author's {$this->handle} entries")) {
            return parent::query();
        }

        return parent::query()->where('author', $user->id);
    }

}
