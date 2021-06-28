<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
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
        // default user
        User::create([
            'name' => "Admin",
            'email' => "admin@admin.cz",
            'role_id' => "admin",
            'status' => "access",
            'password' => Hash::make("admin"),
        ]);
    }
}
