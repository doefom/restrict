<?php

namespace Doefom\Restrict\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Statamic\Facades\User;

class EntryPolicy extends \Statamic\Policies\EntryPolicy
{
    use HandlesAuthorization;

    public function view($user, $entry)
    {
        // If user cannot view other author's entries, check if the entry belongs to the current user.
        if (!$user->isSuper() && !$user->hasPermission("view other author's {$entry->collectionHandle()} entries")) {
            return $user->id === $entry->get('author');
        }

        // Else continue with the default permissions
        return parent::view($user, $entry);
    }

}
