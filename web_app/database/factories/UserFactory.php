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
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'user',
            'referral_code' => strtoupper(Str::random(10)),
            'referred_by' => null,
            'last_login' => fake()->optional(0.7)->dateTimeBetween('-30 days', 'now'),
            'remember_token' => Str::random(10),
        ];
    }
}
