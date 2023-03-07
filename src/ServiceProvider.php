<?php

namespace Doefom\Restrict;

use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{

    protected $policies = [
        \Statamic\Entries\Entry::class => \Doefom\Restrict\Policies\EntryPolicy::class,
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
                Permission::make("view other authors' {collection} entries")
                    ->label("View other authors' entries")
            );
        });
    }

}
