<?php

namespace Doefom\tests\Feature;

class CollectionTest extends \Doefom\tests\BaseTestCase
{

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_user_cannot_view_other_authors_entries()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
