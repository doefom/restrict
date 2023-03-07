<?php

namespace Doefom\Restrict\Tests\Feature;

use Doefom\Restrict\Fieldtypes\Entries;
use Doefom\Restrict\Tests\TestCase;
use Illuminate\Support\Facades\Request;

class EntryPolicyTest extends TestCase
{

    /** @test */
    public function user_a_can_see_other_authors_entries_of_collection_a()
    {
        $this->assertTrue($this->userA->can('view', $this->entryAUserA));
        $this->assertTrue($this->userA->can('view', $this->entryAUserB));
    }

    /** @test */
    public function user_b_cannot_see_other_authors_entries_of_collection_a()
    {
        $this->assertTrue($this->userB->cannot('view', $this->entryAUserA));
        $this->assertTrue($this->userB->can('view', $this->entryAUserB));
    }

}
