<?php

namespace Database\Seeders;

use App\Models\Organisation;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    protected $class = array();
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call($this->getSeeders());
    }

    protected function getSeeders()
    {
        if(config('app.env') != 'production'){
            $this->class = array_merge($this->class, [
                OrganisationSeeder::class,
                DatasetSeeder::class,
            ]);
        }
        return array_merge($this->class, [
            UserSeeder::class,
            UserRoleSeeder::class,
            // Confirm for delete seeder & factory
            // AuthorizationTypeSeeder::class,
        ]);
    }
}
