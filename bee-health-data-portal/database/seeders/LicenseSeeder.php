<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\License;

class LicenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        License::create([
            'label' => 'CC0 1.0',
            'active' => true
        ]);

        License::create([
            'label' => 'CC BY 4.0',
            'order' => 1,
            'active' => true
        ]);

        License::create([
            'label' => 'CC BY-SA 4.0',
            'order' => 2,
            'active' => true
        ]);

        License::create([
            'label' => 'CC BY-ND 4.0',
            'order' => 3,
            'active' => true
        ]);

        License::create([
            'label' => 'CC BY-NC 4.0',
            'order' => 4,
            'active' => true
        ]);

        License::create([
            'label' => 'CC BY-NC-SA 4.0',
            'order' => 5,
            'active' => true
        ]);

        License::create([
            'label' => 'CC BY-NC-ND 4.0',
            'order' => 6,
            'active' => true
        ]);
    }
}
