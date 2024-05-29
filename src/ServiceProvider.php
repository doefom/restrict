<?php

namespace Doefom\Restrict;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('restrict', fn () => new Restrict);
    }

    public function bootAddon(): void
    {
        $this->app->bind(\Statamic\Stache\Query\EntryQueryBuilder::class, function ($app) {
            return new \Doefom\Restrict\Stache\Query\EntryQueryBuilder($app['stache']->store('entries'));
        });
    }
}
