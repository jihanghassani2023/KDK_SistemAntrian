<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => '123qwe123', // akan di-hash otomatis oleh mutator
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);


    }
}
