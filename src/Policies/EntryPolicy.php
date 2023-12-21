<?php

namespace Doefom\Restrict\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Statamic\Facades\User;

class EntryPolicy extends \Statamic\Policies\EntryPolicy
{
    use HandlesAuthorization;

    public function view($user, $entry)
    {
        // If user cannot view other authors' entries, check if the entry belongs to the current user.
        if (!$user->isSuper() && !$user->hasPermission("view other authors' {$entry->collectionHandle()} entries")) {
            return $user->id === $entry->augmentedValue('author')?->raw());
        }

        // Else continue with the default permissions
        return parent::view($user, $entry);
    }

}
