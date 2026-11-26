<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::connection('mariadb')->transaction(function () {
            $this->call([
                RoleSeeder::class,
                PermissionSeeder::class,
                RolePermissionSeeder::class,
                AdminUserSeeder::class,
            ]);
        });
    }
}