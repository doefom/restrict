<?php

namespace Doefom\Restrict\Policies;

use Statamic\Facades\User;
use Statamic\Policies\EntryPolicy as StatamicEntryPolicy;

class EntryPolicy extends StatamicEntryPolicy
{
    public function view($user, $entry)
    {
        $user = User::fromUser($user);

        $default = parent::view($user, $entry);

        if ($this->hasAnotherAuthor($user, $entry)) {
            return $default && $user->hasPermission("view other authors' {$entry->collectionHandle()} entries");
        }

        return $default;
    }
}
