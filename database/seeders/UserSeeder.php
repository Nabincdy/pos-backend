<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => 'password',
            'status_at' => now(),
            'phone' => '98......',
            'role_id' => 1,
        ]);
    }
}
