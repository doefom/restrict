<?php

namespace Doefom\Restrict;

use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;
use Statamic\Tags\Collection\Collection;

class ServiceProvider extends AddonServiceProvider
{

    public function bootAddon(): void
    {
        Statamic::repository(
            \Statamic\Contracts\Entries\EntryRepository::class,
            \Doefom\Restrict\Contracts\Entries\EntryRepository::class
        );

        $this->app->bind(
            \Statamic\Policies\EntryPolicy::class,
            \Doefom\Restrict\Policies\EntryPolicy::class
        );

        Permission::extend(function () {
            Permission::get("view {collection} entries")->addChild(
                Permission::make("view other authors' {collection} entries")
                    ->label("View other authors' entries")
            );
        });
    }

}
