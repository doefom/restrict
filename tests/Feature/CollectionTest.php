<?php

namespace Doefom\Restrict\Tests\Feature;

use Doefom\Restrict\Tests\TestCase;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Role;
use Statamic\Facades\User;

class CollectionTest extends TestCase
{
    /** @test */
    public function dummy_test()
    {

        // Create blueprint
        $blueprintA = $this->createBlueprintFromFixtures('/blueprints/collections/a/a.yaml');
        $blueprintB = $this->createBlueprintFromFixtures('/blueprints/collections/b/b.yaml');

        // Create collection
        $collectionA = Collection::make('a')->save();
        $collectionB = Collection::make('b')->save();

        // Create role
        $viewOtherAuthorsCollectionEntries = Role::make()
            ->handle('a')
            ->title('A')
            ->addPermission("access cp")
            ->addPermission("view {$collectionA->handle()} entries")
            ->addPermission("view other author's {$collectionA->handle()} entries");
        $viewOtherAuthorsCollectionEntries->save();

        // Create user A
        $userA = User::make()
            ->data([
                'name' => 'User A',
                'email' => 'a@example.org',
                'password' => 'a',
                'super' => false,
            ]);
        $userA->assignRole('author');
        $userA->save();

        // Create user B
        $userB = User::make()
            ->data([
                'name' => 'User B',
                'email' => 'b@example.org',
                'password' => 'b',
                'super' => false,
            ]);
        $userB->save();

        // Create entry for user A in collection A
        Entry::make()
            ->collection($collectionA->handle())
            ->blueprint($blueprintA->handle())
            ->data([
                'title' => "Test",
                'author' => $userA->id
            ])
            ->save();

        // Create entry for user B in collection A
        Entry::make()
            ->collection($collectionA->handle())
            ->blueprint($blueprintA->handle())
            ->data([
                'title' => "Test",
                'author' => $userA->id
            ])
            ->save();

        // Note
        // User A can see all entries of collection A.
        // User B must not see User A's entries in collection A.

        // TODO: Actually test stuff here.

        $this->assertEquals(2, Entry::all()->count());

    }

}
