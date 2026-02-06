<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Order;
use App\Models\TravelerProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::factory()->create([
            'name' => 'Admin JastipKu',
            'email' => 'admin@gmail.com',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);
    }
}
