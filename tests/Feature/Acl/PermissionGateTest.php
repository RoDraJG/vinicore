<?php

namespace Tests\Feature\Acl;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PermissionGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_has_core_permission(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::query()->first();

        $this->assertNotNull($user);
        $this->assertTrue(Gate::check('check-permission', [$user, 'ernte_ansehen']));
    }
}
