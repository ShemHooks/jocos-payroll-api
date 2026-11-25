<?php

namespace Database\Seeders;

use App\Enums\RoleCode;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RoleCode::cases() as $role) {
            Role::updateOrCreate(
                [
                    'code' => $role->value,
                ],
                [
                    'name' => $role->label(),
                    'description' => $role->description(),
                ]
            );
        }

        $this->command?->info(
            'System roles initialized successfully.'
        );
    }
}