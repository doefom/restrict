<?php

namespace Doefom\Restrict;

use Doefom\Restrict\Stache\Query\EntryQueryBuilder;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon(): void
    {
        // ------------------------------------------------------------------------------------
        // Bind the entry query builder
        // ------------------------------------------------------------------------------------

        $this->app->bind(\Statamic\Stache\Query\EntryQueryBuilder::class, function ($app) {
            return new EntryQueryBuilder($app['stache']->store('entries'));
        });
    }
}
