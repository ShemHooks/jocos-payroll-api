<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'username' => $this->username,

            'email' => $this->email,

            'is_active' => (bool) $this->is_active,

            'must_change_password' =>
                (bool) $this->must_change_password,

            'roles' => $this->roles
                ->map(fn($role) => [
                    'id' => $role->id,
                    'code' => $role->code,
                    'name' => $role->name,
                ])
                ->values()
                ->all(),

            'permissions' => $this->getPermissionCodes(),

            'password_changed_at' =>
                $this->password_changed_at?->toIso8601String(),

            'created_at' =>
                $this->created_at?->toIso8601String(),
        ];
    }
}