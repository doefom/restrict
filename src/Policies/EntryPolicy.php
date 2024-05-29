<?php

namespace Doefom\Restrict\Policies;

use Doefom\Restrict\Facades\Restrict;
use Statamic\Facades\User;
use Statamic\Policies\EntryPolicy as StatamicEntryPolicy;

class EntryPolicy extends StatamicEntryPolicy
{
    public function view($user, $entry)
    {
        $user = User::fromUser($user);

        $default = parent::view($user, $entry);

        if (! Restrict::shouldApplyRestriction($user)) {
            return $default;
        }

        return $default && Restrict::isAuthorized($user, $entry);
    }
}
