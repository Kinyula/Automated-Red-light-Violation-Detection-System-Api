<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
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
        // Ensure that 'first_name' is unique and 'email' is unique
        // Note: 'first_name' should not be unique, so we use 'name()' instead of 'unique()->name()'
        return [
            'first_name' => fake()->name(),
            'last_name' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'email' => fake()->unique()->safeEmail(),
            'is_verified' => true,
            'online_status' => 'offline',
            'last_activity' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'phone_number' => fake()->phoneNumber(),
            'license_plate' => fake()->unique()->bothify('??###??'),
            'role_id' => '0',
            'position' => 'driver',
            'department' => 'none',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
