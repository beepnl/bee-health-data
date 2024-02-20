<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'firstname' => config('auth.admin.firstname'),
            'lastname' => config('auth.admin.lastname'),
            'email' => config('auth.admin.email'),
            'password' => Hash::make(config('auth.admin.password', 'admin')),
            'accepted_terms_and_conditions' => '',
            'email_verified_at' => now(),
            'last_login' => null,
            'api_token' => Str::random(60),
            'is_admin' => true,
            'remember_token' => Str::random(100),
        ]);
    }
}
