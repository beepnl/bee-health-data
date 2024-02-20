<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make($this->faker->password),
            'accepted_terms_and_conditions' => '1',
            'email_verified_at' => now(),
            'last_login' => $this->faker->dateTimeBetween('-30 years', 'now'),
            'api_token' => Str::random(60),
            'is_admin' => $this->faker->boolean,
            'is_active' => $this->faker->boolean,
            'remember_token' => Str::random(100),
        ];
    }
}
