<?php

namespace Database\Seeders;

use App\Models\UserRole;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserRole::create([
            'role_name' => "admin",
        ]);

        UserRole::create([
            'role_name' => "editor"
        ]);

        UserRole::create([
            'role_name' => "view"
        ]);
    }
}
