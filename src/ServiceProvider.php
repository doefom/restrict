<?php

namespace Doefom\Restrict;

use Doefom\Restrict\Policies\EntryPolicy;
use Statamic\Entries\Entry;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{

    protected $policies = [
        Entry::class => EntryPolicy::class,
    ];

    public function register()
    {

        $this->app->bind(
            \Statamic\Entries\Collection::class,
            \Doefom\Restrict\Entries\Collection::class
        );

        $this->app->bind(
            \Statamic\Fieldtypes\Entries::class,
            \Doefom\Restrict\Fieldtypes\Entries::class
        );

    }

    public function bootAddon()
    {
        Permission::extend(function () {
            Permission::get("view {collection} entries")->addChild(
                Permission::make("view other author's {collection} entries")
                    ->label("View other author's entries")
            );
        });
    }

}
