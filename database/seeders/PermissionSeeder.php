<?php

namespace Database\Seeders;

use App\Enums\PermissionCode;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionCode::cases() as $permission) {
            Permission::updateOrCreate(
                [
                    'code' => $permission->value,
                ],
                [
                    'description' =>
                        $permission->description(),
                ]
            );
        }

        $this->command?->info(
            'System permissions initialized successfully.'
        );
    }
}