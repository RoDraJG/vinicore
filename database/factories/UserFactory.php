<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'betrieb_id' => 1,
            'username' => fake()->unique()->userName(),
            'name' => fake()->name(),
            'password' => static::$password ??= Hash::make('password'),
            'is_hauptnutzer' => false,
            'erlaubte_gemarkungen' => json_encode(['Allgemein']),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Keep compatibility with older auth tests that expect an unverified state.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_hauptnutzer' => false,
        ]);
    }
}
