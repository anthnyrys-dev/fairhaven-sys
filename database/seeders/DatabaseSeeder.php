<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Fairhaven Office',
            'email' => 'office@fairhaven.test',
        ]);

        Artisan::call('fairhaven:import');
        $this->command->info(trim(Artisan::output()));
    }
}
