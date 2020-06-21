<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\SolarProject;

class SolarProjectsTest extends TestCase
{
    public function testIndex()
    {
        $response = $this->get('/api/solar_projects');

        $response->assertStatus(200)
            ->assertJson([
                'links' => [
                    'next' => url('/api/solar_projects') . '?page=2',
                    'prev' => null,
                ],
            ]);
    }

    public function testShow()
    {
        $id = SolarProject::first()->uuid;

        $response = $this->json('GET', "/api/solar_projects/$id");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'solar_projects',
                    'id' => $id,
                ],
            ]);
    }

    public function testIncompletePut()
    {
        $id = SolarProject::first()->uuid;

        $newTitle = 'wont work';
        $response = $this->json('PUT', "/api/solar_projects/$id", [
            'title' => $newTitle,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'title' => 'Validation Exception',
                'errors' => [
                    'system_size' => ['The system size field must be present.'],
                    'site_latitude' => ['The site latitude field is required.'],
                    'site_longitude' => ['The site longitude field is required.'],
                ],
            ]);
    }

    public function testPatch()
    {
        $id = SolarProject::first()->uuid;

        $newTitle = 'test title ' . now()->toAtomString();
        $response = $this->json('PATCH', "/api/solar_projects/$id", [
            'title' => $newTitle,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'attributes' => [
                        'title' => $newTitle,
                    ],
                ],
            ]);
    }

    /**
     * Test function for bulk delete of SolarProjects
     */

    public function testDestroyMany()
    {
        $ids = SolarProject::take(5)->pluck('uuid');

        $response = $this->json('DELETE', "/api/solar_projects", [
            'ids' => $ids
        ]);

        $response->assertStatus(200);
    }
}
