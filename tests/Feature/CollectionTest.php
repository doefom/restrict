<?php

namespace Doefom\tests\Feature;

use Illuminate\Contracts\Auth\Authenticatable;

class CollectionTest extends \Doefom\tests\BaseTestCase
{

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_user_cannot_view_other_authors_entries()
    {
        $response = $this->actingAs($this->authorB)
            ->withSession(['banned' => false])
            ->get('/cp/collections');

        $response->assertStatus(200);
    }
}
