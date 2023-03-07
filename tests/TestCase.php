<?php

namespace Doefom\Restrict\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Extend\Manifest;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Facades\YAML;
use Statamic\Statamic;

abstract class TestCase extends OrchestraTestCase
{

    use PreventSavingStacheItemsToDisk;

    public \Statamic\Fields\Blueprint $blueprintA;
    public \Statamic\Fields\Blueprint $blueprintB;
    public \Statamic\Entries\Collection $collectionA;
    public \Statamic\Entries\Collection $collectionB;
    public \Statamic\Contracts\Auth\Role $roleA;
    public \Statamic\Contracts\Auth\Role $roleB;
    public \Statamic\Contracts\Auth\User $userA;
    public \Statamic\Contracts\Auth\User $userB;
    public \Statamic\Contracts\Auth\User $userC;
    public \Statamic\Entries\Entry $entryAUserA; // Entry in collection A with author user A
    public \Statamic\Entries\Entry $entryAUserB; // Entry in collection A with author user B
    public \Statamic\Entries\Entry $entryAUserC; // Entry in collection A with author user C

    public function setup(): void
    {
        parent::setup();
        $this->preventSavingStacheItemsToDisk();
        $this->createSetupData();
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

        $app['config']->set('statamic.users.repository', 'file');
        $app['config']->set('statamic.stache.stores.users', [
            'class' => \Statamic\Stache\Stores\UsersStore::class,
            'directory' => __DIR__ . '/__fixtures__/users',
        ]);

        $app['config']->set('statamic.stache.stores.taxonomies.directory', __DIR__ . '/__fixtures__/content/taxonomies');
        $app['config']->set('statamic.stache.stores.terms.directory', __DIR__ . '/__fixtures__/content/taxonomies');
        $app['config']->set('statamic.stache.stores.collections.directory', __DIR__ . '/__fixtures__/content/collections');
        $app['config']->set('statamic.stache.stores.entries.directory', __DIR__ . '/__fixtures__/content/collections');
        $app['config']->set('statamic.stache.stores.navigation.directory', __DIR__ . '/__fixtures__/content/navigation');
        $app['config']->set('statamic.stache.stores.globals.directory', __DIR__ . '/__fixtures__/content/globals');
        $app['config']->set('statamic.stache.stores.asset-containers.directory', __DIR__ . '/__fixtures__/content/assets');
        $app['config']->set('statamic.stache.stores.nav-trees.directory', __DIR__ . '/__fixtures__/content/structures/navigation');
        $app['config']->set('statamic.stache.stores.collection-trees.directory', __DIR__ . '/__fixtures__/content/structures/collections');

    }

    protected function resolveApplicationConfiguration($app)
    {

        parent::resolveApplicationConfiguration($app);

        $configs = [
            'assets', 'cp', 'forms', 'static_caching',
            'sites', 'stache', 'system', 'users',
        ];

        foreach ($configs as $config) {
            $app['config']->set("statamic.$config", require(__DIR__ . "/../vendor/statamic/cms/config/{$config}.php"));
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

    /**
     * Create setup data for all tests.
     *
     * This function creates the following data:
     * - Two blueprints (that both have an 'author' field type)
     * - Two collections (with one blueprint each)
     * - Two roles, where role A can view other author's collection entries but role B can view own entries only
     * - Two users, where user A is assigned role A and user B is assigned role B
     * - Two entries (one for each collection)
     *
     * User A:
     * - Can view other author's entries in collection A
     * - Has one own entry in collection A
     * - Has one own entry in collection B
     *
     * User B:
     * - Cannot view other author's entries in collection A
     * - Has one own entry in collection A
     * - Has one own entry in collection B
     *
     * User C:
     * - Is super user and can see everything
     *
     * @return void
     */
    public function createSetupData()
    {
        // Create blueprints
        $this->blueprintA = $this->createBlueprintFromFixtures('/blueprints/collections/a/a.yaml');
        $this->blueprintB = $this->createBlueprintFromFixtures('/blueprints/collections/b/b.yaml');

        // Create collections
        $this->collectionA = Collection::make('a')->save();
        $this->collectionB = Collection::make('b')->save();

        // Create role - View other author's collection entries
        $this->roleA = Role::make()
            ->handle('a')
            ->title('A')
            ->addPermission("access cp")
            ->addPermission("view {$this->collectionA->handle()} entries")
            ->addPermission("view other author's {$this->collectionA->handle()} entries");
        $this->roleA->save();

        // Create role - View own collection entries only
        $this->roleB = Role::make()
            ->handle('b')
            ->title('B')
            ->addPermission("access cp")
            ->addPermission("view {$this->collectionB->handle()} entries");
        $this->roleB->save();

        // Create user A
        $this->userA = User::make()
            ->data([
                'name' => 'User A',
                'email' => 'a@example.org',
                'password' => 'a',
                'super' => false,
            ]);
        $this->userA->assignRole($this->roleA);
        $this->userA->save();

        // Create user B
        $this->userB = User::make()
            ->data([
                'name' => 'User B',
                'email' => 'b@example.org',
                'password' => 'b',
                'super' => false,
            ]);
        $this->userB->assignRole($this->roleB);
        $this->userB->save();

        // Create user C (Super User)
        $this->userC = User::make()
            ->data([
                'name' => 'User C',
                'email' => 'c@example.org',
                'password' => 'c',
                'super' => true,
            ]);
        $this->userC->save();

        // Create entry for user A in collection A
        $this->entryAUserA = Entry::make()
            ->collection($this->collectionA->handle())
            ->blueprint($this->blueprintA->handle())
            ->data([
                'title' => "Test",
                'author' => $this->userA->id
            ]);
        $this->entryAUserA->save();

        // Create entry for user B in collection A
        $this->entryAUserB = Entry::make()
            ->collection($this->collectionA->handle())
            ->blueprint($this->blueprintA->handle())
            ->data([
                'title' => "Test",
                'author' => $this->userB->id
            ]);
        $this->entryAUserB->save();

        // Create entry for user C in collection A
        $this->entryAUserC = Entry::make()
            ->collection($this->collectionA->handle())
            ->blueprint($this->blueprintA->handle())
            ->data([
                'title' => "Test",
                'author' => $this->userC->id
            ]);
        $this->entryAUserC->save();
    }

}
