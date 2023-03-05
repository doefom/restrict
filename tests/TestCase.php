<?php

namespace Doefom\Restrict\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Extend\Manifest;
use Statamic\Facades\Blueprint;
use Statamic\Facades\YAML;
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

//        $app['config']->set('statamic.users.repository', 'file');
//        $app['config']->set('statamic.stache.stores.users', [
//            'class' => \Statamic\Stache\Stores\UsersStore::class,
//            'directory' => __DIR__ . '/__fixtures__/users',
//        ]);

//        $app['config']->set('statamic.stache.stores.taxonomies.directory', __DIR__ . '/__fixtures__/content/taxonomies');
//        $app['config']->set('statamic.stache.stores.terms.directory', __DIR__ . '/__fixtures__/content/taxonomies');
//        $app['config']->set('statamic.stache.stores.collections.directory', __DIR__ . '/__fixtures__/content/collections');
//        $app['config']->set('statamic.stache.stores.entries.directory', __DIR__ . '/__fixtures__/content/collections');
//        $app['config']->set('statamic.stache.stores.navigation.directory', __DIR__ . '/__fixtures__/content/navigation');
//        $app['config']->set('statamic.stache.stores.globals.directory', __DIR__ . '/__fixtures__/content/globals');
//        $app['config']->set('statamic.stache.stores.asset-containers.directory', __DIR__ . '/__fixtures__/content/assets');
//        $app['config']->set('statamic.stache.stores.nav-trees.directory', __DIR__ . '/__fixtures__/content/structures/navigation');
//        $app['config']->set('statamic.stache.stores.collection-trees.directory', __DIR__ . '/__fixtures__/content/structures/collections');

    }

    protected function resolveApplicationConfiguration($app)
    {

        parent::resolveApplicationConfiguration($app);

        $configs = [
            'assets', 'cp', 'forms', 'static_caching',
            'sites', 'stache', 'system', 'users',
        ];

        foreach ($configs as $config) {
            $app['config']->set("statamic.$config", require(__DIR__."/../vendor/statamic/cms/config/{$config}.php"));
        }

        $app['config']->set('statamic.users.repository', 'file');

    }

    public function createBlueprintFromFixtures(string $path)
    {
        $blueprintContents = YAML::parse(file_get_contents(__DIR__ . '/__fixtures__' . $path));
        $blueprintFields = collect($blueprintContents['sections']['main']['fields'])
            ->keyBy(fn($item) => $item['handle'])
            ->map(fn($item) => $item['field'])
            ->all();

        $blueprint = Blueprint::makeFromFields($blueprintFields)
            ->setNamespace('collections')
            ->setHandle('a');
        $blueprint->save();

        return $blueprint;
    }

}
