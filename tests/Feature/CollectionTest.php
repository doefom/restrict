<?php

namespace Doefom\Restrict\Tests\Feature;

use Doefom\Restrict\Tests\TestCase;

class CollectionTest extends TestCase
{

    /** @test */
    public function user_a_can_see_other_authors_entries_of_collection_a()
    {
        $this->actingAs($this->userA);
        $this->assertEquals(3, $this->collectionA->queryEntries()->count());
    }

    /** @test */
    public function user_b_cannot_see_other_authors_entries_of_collection_a()
    {
        $this->actingAs($this->userB);
        $this->assertEquals(1, $this->collectionA->queryEntries()->count());
    }

    /** @test */
    public function user_c_can_see_all_entries_of_collection_a()
    {
        $this->actingAs($this->userC);
        $this->assertEquals(3, $this->collectionA->queryEntries()->count());
    }

}
