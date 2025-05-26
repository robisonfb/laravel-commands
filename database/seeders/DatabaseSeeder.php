<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'first_name' => 'Robison',
            'last_name'  => 'Borges',
            'email'      => 'robison@sevenclick.com.br',
            'password'   => bcrypt('12345678'),
        ]);
    }
}
