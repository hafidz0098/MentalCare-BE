<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Topik;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        User::create([
            'name' => 'Hafidz',
            'email' => 'hafidznak123@gmail.com',
            'password' => bcrypt('12345'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Psikolog',
            'email' => 'psikolog@gmail.com',
            'password' => bcrypt('12345'),
            'role' => 'psikolog',
        ]);

        User::create([
            'name' => 'User',
            'email' => 'user@gmail.com',
            'password' => bcrypt('12345'),
            'role' => 'user',
        ]);
    }
}
