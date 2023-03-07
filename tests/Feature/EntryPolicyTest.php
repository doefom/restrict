<?php

namespace Doefom\Restrict\Tests\Feature;

use Doefom\Restrict\Tests\TestCase;

class EntryPolicyTest extends TestCase
{

    /** @test */
    public function user_a_can_see_other_authors_entries_of_collection_a()
    {
        $this->assertTrue($this->userA->can('view', $this->entryAUserA));
        $this->assertTrue($this->userA->can('view', $this->entryAUserB));
        $this->assertTrue($this->userA->can('view', $this->entryAUserC));
    }

    /** @test */
    public function user_b_cannot_see_other_authors_entries_of_collection_a()
    {
        $this->assertTrue($this->userB->cannot('view', $this->entryAUserA));
        $this->assertTrue($this->userB->can('view', $this->entryAUserB));
        $this->assertTrue($this->userB->cannot('view', $this->entryAUserC));
    }

    /** @test */
    public function user_c_can_see_other_authors_entries_of_collection_a()
    {
        $this->assertTrue($this->userC->can('view', $this->entryAUserA));
        $this->assertTrue($this->userC->can('view', $this->entryAUserB));
        $this->assertTrue($this->userC->can('view', $this->entryAUserC));
    }

}
