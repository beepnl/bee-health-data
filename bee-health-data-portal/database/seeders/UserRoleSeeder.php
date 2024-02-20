<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_role')->insert([
            'id' => Uuid::uuid1()->toString(),
            'name' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('user_role')->insert([
            'id' => Uuid::uuid1()->toString(),
            'name' => 'organisation admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
