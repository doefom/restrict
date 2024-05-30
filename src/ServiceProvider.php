<?php

namespace Doefom\Restrict;

use Doefom\Restrict\Stache\Query\EntryQueryBuilder;
use Illuminate\Support\Facades\Log;
use Statamic\Policies\EntryPolicy;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('restrict', fn () => new Restrict);
    }

    public function bootAddon(): void
    {
        // ------------------------------------------------------------------------------------
        // Register the config file
        // ------------------------------------------------------------------------------------

        $this->publishes([
            __DIR__.'/../config/restrict.php' => config_path('restrict.php'),
        ], 'restrict-config');

        // ------------------------------------------------------------------------------------
        // Try to bind the configured policy
        // ------------------------------------------------------------------------------------

        $entryPolicy = config('restrict.entry_policy');

        if (! $entryPolicy) {
            Log::warning('Restrict addon (doefom/restrict) is installed but no entry policy is configured. Please configure an entry policy in the restrict.php config file.');

            return;
        }

        $this->app->bind(EntryPolicy::class, $entryPolicy);

        // ------------------------------------------------------------------------------------
        // Bind the entry query builder
        // ------------------------------------------------------------------------------------

        $this->app->bind(\Statamic\Stache\Query\EntryQueryBuilder::class, function ($app) {
            return new EntryQueryBuilder($app['stache']->store('entries'));
        });
    }
}
