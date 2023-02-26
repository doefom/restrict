<?php

namespace Doefom\Restrict\Entries;

use Statamic\Facades\User;

class Collection extends \Statamic\Entries\Collection
{

    public function queryEntries()
    {
        $user = User::current();

        if (!$user || $user->isSuper() || $user->hasPermission("view other author's $this->handle entries")) {
            return parent::queryEntries();
        }

        return parent::queryEntries()->where('author', $user->id);
    }

}
