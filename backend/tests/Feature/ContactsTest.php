<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Contact;

class ContactsTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testUpdate()
    {
        $contactId = Contact::first()->uuid;

        $response = $this->json('PUT', "/api/contacts/$contactId", [
            'first_name' => ''
        ]);

        $response->assertStatus(200);
    }
}
