<?php

namespace Doefom\Restrict\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Extend\Manifest;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;

abstract class TestCase extends OrchestraTestCase
{

    use PreventSavingStacheItemsToDisk;

    public function setup(): void
    {
        parent::setup();
        $this->preventSavingStacheItemsToDisk();
    }

    public function tearDown(): void
    {
        $this->deleteFakeStacheDirectory();

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            \Statamic\Providers\StatamicServiceProvider::class,
            \Doefom\Restrict\ServiceProvider::class
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->make(Manifest::class)->manifest = [
            'doefom/restrict' => [
                'id' => 'doefom/restrict',
                'namespace' => 'Doefom\\Restrict',
            ],
        ];

        config(['statamic.users.repository' => 'file']);
    }

}
