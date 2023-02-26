<?php

namespace Doefom\tests;

use Illuminate\Support\Facades\File;
use Statamic\Entries\EntryCollection;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Support\Str;

class BaseTestCase extends \Tests\TestCase
{

    public $blueprintA;
    public $blueprintB;

    public \Statamic\Contracts\Entries\Collection $collectionA;

    public \Statamic\Contracts\Entries\Collection $collectionB;

    public \Statamic\Contracts\Auth\Role $roleViewOtherAuthorsEntriesCollectionA;

    public \Statamic\Contracts\Auth\User $authorA;

    public \Statamic\Contracts\Auth\User $authorB;

    public \Statamic\Contracts\Auth\User $superAdmin;

    public EntryCollection $entriesA;
    public EntryCollection $entriesB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collectionA = $this->createCollection('A');
        $this->collectionB = $this->createCollection('B');

        $this->authorA = $this->createUser(); // Note: User A has entries but has no permissions (e.g. cannot access cp)
        $this->authorB = $this->createUser();
        $this->superAdmin = $this->createUser(true);

        $this->roleViewOtherAuthorsEntriesCollectionA = $this->createRole("ViewOtherAuthorsEntriesCollectionA", $this->collectionA);
        $this->authorB->assignRole($this->roleViewOtherAuthorsEntriesCollectionA->handle());

        $this->blueprintA = $this->createBlueprint('default', $this->collectionA);
        $this->blueprintB = $this->createBlueprint('default', $this->collectionB);

        // Create entry for collectionA
        $this->createEntryForUser($this->authorA, $this->collectionA, $this->blueprintA);

        // Create entry for collectionB
        $this->createEntryForUser($this->authorB, $this->collectionB, $this->blueprintB);

        // Save the entries in variables for easy access in tests
        $this->entriesA = Entry::query()->where('collection', $this->collectionA->handle())->get();
        $this->entriesB = Entry::query()->where('collection', $this->collectionB->handle())->get();
    }

    public function tearDown(): void
    {
        Entry::query()->where('collection', $this->collectionA->handle())->get()->each->delete();
        Entry::query()->where('collection', $this->collectionB->handle())->get()->each->delete();

        $this->collectionA->delete();
        $this->collectionB->delete();

        $this->authorA->delete();
        $this->authorB->delete();
        $this->superAdmin->delete();

        $this->roleViewOtherAuthorsEntriesCollectionA->delete();

        $this->blueprintA->delete();
        $this->blueprintB->delete();

        // Delete remaining directories
        File::deleteDirectory(resource_path("blueprints/collections/{$this->collectionA->handle()}"));
        File::deleteDirectory(resource_path("blueprints/collections/{$this->collectionB->handle()}"));
    }

    public function createCollection(string $title): \Statamic\Entries\Collection
    {
        $collection = Collection::make(Str::lower($title));
        $collection->title($title);
        $collection->save();

        return $collection;
    }

    public function createUser(bool $super = false): \Statamic\Contracts\Auth\User
    {
        $user = User::make()->data([
            'name' => fake()->name,
            'email' => fake()->email,
            'password' => fake()->password,
            'super' => $super,
        ]);

        $user->save();

        return $user;
    }

    public function createRole(string $title, \Statamic\Contracts\Entries\Collection $collection)
    {
        $role = Role::make()
            ->handle(Str::lower($title))
            ->title($title)
            ->addPermission("access cp")
            ->addPermission("view {$collection->handle()} entries")
            ->addPermission("view other author's {$collection->handle()} entries");

        $role->save();

        return $role;
    }

    public function createBlueprint(string $title, \Statamic\Contracts\Entries\Collection $collection)
    {
        $blueprint = (new \Statamic\Fields\Blueprint)
            ->setHandle(Str::lower($title))
            ->setNamespace("collections.{$collection->handle()}")
            ->setContents([
                'title' => $title,
                'sections' => [
                    'main' => [
                        'display' => __('Main'),
                        'fields' => [
                            [
                                'handle' => 'title',
                                'field' => [
                                    'type' => 'text',
                                    'required' => true,
                                    'validate' => ['required'],
                                ],
                            ],
                            [
                                'handle' => 'author',
                                'field' => [
                                    'type' => 'users',
                                    'display' => 'Author',
                                    'default' => 'current',
                                    'localizable' => true,
                                    'max_items' => 1,
                                ],
                            ]
                        ],
                    ],
                ],
            ]);

        $blueprint->save();

        return $blueprint;
    }

    public function createEntryForUser(\Statamic\Contracts\Auth\User $user, \Statamic\Contracts\Entries\Collection $collection, \Statamic\Fields\Blueprint $blueprint)
    {
        $entry = Entry::make()
            ->collection($collection->handle())
            ->blueprint($blueprint->handle())
            ->data([
                'title' => fake()->realTextBetween(20, 40),
                'author' => $this->authorA->id()
            ]);

        $entry->save();

        return $entry;
    }

}
