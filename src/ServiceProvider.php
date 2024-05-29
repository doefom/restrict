<?php

namespace Doefom\Restrict;

use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('restrict', fn () => new Restrict);
    }

    public function bootAddon(): void
    {
        Statamic::repository(
            \Statamic\Contracts\Entries\EntryRepository::class,
            \Doefom\Restrict\Stache\Repositories\EntryRepository::class
        );
    }
}
