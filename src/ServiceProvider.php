<?php

namespace Doefom\Restrict;

use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    public function register()
    {
        $this->app->singleton('restrict', fn() => new Restrict);
    }

    public function bootAddon(): void
    {
        Statamic::repository(
            \Statamic\Contracts\Entries\EntryRepository::class,
            \Doefom\Restrict\Stache\Repositories\EntryRepository::class
        );

        // Note: This is just a precaution, as the EntryRepository would already make sure that unauthorized entries
        // are not returned in the first place and can therefore not be checked by the EntryPolicy.
        $this->app->bind(
            \Statamic\Policies\EntryPolicy::class,
            \Doefom\Restrict\Policies\EntryPolicy::class
        );
    }
}
