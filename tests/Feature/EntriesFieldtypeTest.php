<?php

namespace Doefom\Restrict\Tests\Feature;

use Doefom\Restrict\Fieldtypes\Entries;
use Doefom\Restrict\Tests\TestCase;
use Illuminate\Support\Facades\Request;

class EntriesFieldtypeTest extends TestCase
{

    /** @test */
    public function user_a_can_see_other_authors_entries_of_collection_a()
    {
        $this->actingAs($this->userA);
        $request = Request::create('/cp/fieldtypes/relationship');
        $this->assertEquals(3, (new Entries())->getIndexQuery($request)->count());
    }

    /** @test */
    public function user_b_cannot_see_other_authors_entries_of_collection_a()
    {
        $this->actingAs($this->userB);
        $request = Request::create('/cp/fieldtypes/relationship');
        $this->assertEquals(1, (new Entries())->getIndexQuery($request)->count());
    }

    /** @test */
    public function user_c_can_see_other_authors_entries_of_collection_a()
    {
        $this->actingAs($this->userC);
        $request = Request::create('/cp/fieldtypes/relationship');
        $this->assertEquals(3, (new Entries())->getIndexQuery($request)->count());
    }

}
