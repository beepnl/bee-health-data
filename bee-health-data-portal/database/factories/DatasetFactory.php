<?php

namespace Database\Factories;

use App\Models\Dataset;
use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Organisation;
use App\Models\User;

class DatasetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Dataset::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'description' => $this->faker->jobTitle,
            'digital_object_identifier' => $this->faker->phoneNumber,
            'organisation_id' => Organisation::factory(),
            'user_id' => User::factory(),
            'publication_state' => 'draft',
            'access_type' => 'owning_organisation_only',
            'number_files' => rand(0, 100),
        ];
    }
}
