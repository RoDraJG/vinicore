<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase0ApiRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_ernte_kampagnen_route_returns_successful_json(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/v1/ernte/kampagnen');

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
        ]);
    }

    public function test_personal_worker_route_returns_successful_json(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/v1/personal/arbeitskraefte');

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
        ]);
    }
}
