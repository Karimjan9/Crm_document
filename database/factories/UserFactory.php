<?php

namespace Database\Factories;

use App\Models\FilialModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'login' => $this->faker->unique()->userName(),
            'phone' => '998' . $this->faker->unique()->numerify('#########'),
            'filial_id' => FilialModel::query()->inRandomOrder()->value('id'),
            'password' => static::$password ??= Hash::make('password'),
            'avatar_path' => null,
            'settings' => [
                'seeded' => true,
                'type' => 'factory',
            ],
            'remember_token' => Str::random(10),
        ];
    }
}
