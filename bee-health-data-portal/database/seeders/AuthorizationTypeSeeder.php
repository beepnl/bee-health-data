<?php

namespace Database\Seeders;

use App\Models\AuthorizationType;
use Illuminate\Database\Seeder;

class AuthorizationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AuthorizationType::factory()->create([
            'name' => 'organisation requests'
        ]);

        AuthorizationType::factory()->create([
            'name' => 'user requests'
        ]);
    }
}
