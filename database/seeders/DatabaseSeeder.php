<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create HRD User
        User::updateOrCreate(
            ['email' => 'teshrd@company.com'],
            [
                'name' => 'HRD Admin',
                'password' => Hash::make('12345678'),
            ]
        );
        User::updateOrCreate(
            ['email'=> 'teshrd2@company.com'],
            [
                'name' => "HRD 2", 
                'password' => Hash::make('11111111'),
            ]
        );
    }
}
