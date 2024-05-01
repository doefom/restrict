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

        $canViewOtherAuthorsEntries = $user->hasPermission("view other authors' {$entry->collectionHandle()} entries");
        $isAuthorOfThisEntry = $entry->get('author') === $user->id();

        return $default && ($isAuthorOfThisEntry || $canViewOtherAuthorsEntries);
    }

}
