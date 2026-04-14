<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'email' => 'adrian88gm@gmail.com',
        ], [
            'name' => 'Adrian',
            'email' => 'adrian88gm@gmail.com',
            'password' => Hash::make('laxoso12x'),
            'email_verified_at' => now(),
        ]);

        $this->call([
            BaseCatalogsSeeder::class,
            DemoRestauranteSeeder::class,
            DemoBodegaSeeder::class,
        ]);
    }
}
