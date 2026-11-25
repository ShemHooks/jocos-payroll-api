<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOrganizationalUnit extends Model
{
    use HasFactory;

    protected $table = 'user_organizational_units';

    protected $fillable = [
        'user_id',
        'organizational_unit_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(
            OrganizationalUnit::class
        );
    }
}