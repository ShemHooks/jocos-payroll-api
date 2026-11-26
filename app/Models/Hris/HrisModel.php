<?php

namespace App\Models\Hris;

use Illuminate\Database\Eloquent\Model;
use LogicException;

abstract class HrisModel extends Model
{
    /*
    |--------------------------------------------------------------------------
    | HRIS Database Connection
    |--------------------------------------------------------------------------
    */

    protected $connection = 'hris';

    /*
    |--------------------------------------------------------------------------
    | Default Model Configuration
    |--------------------------------------------------------------------------
    */

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = false;

    protected $guarded = ['*'];

    /*
    |--------------------------------------------------------------------------
    | Read-Only Model Protection
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saving(function (Model $model) {
            throw new LogicException(
                'HRIS records are read-only. Saving is not allowed.'
            );
        });

        static::deleting(function (Model $model) {
            throw new LogicException(
                'HRIS records are read-only. Deleting is not allowed.'
            );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Prevent Instance-Level Write Operations
    |--------------------------------------------------------------------------
    */

    public function save(array $options = [])
    {
        throw new LogicException(
            'HRIS records are read-only. Saving is not allowed.'
        );
    }

    public function delete()
    {
        throw new LogicException(
            'HRIS records are read-only. Deleting is not allowed.'
        );
    }

    public function increment(
        $column,
        $amount = 1,
        array $extra = []
    ) {
        throw new LogicException(
            'HRIS records are read-only. Incrementing is not allowed.'
        );
    }

    public function decrement(
        $column,
        $amount = 1,
        array $extra = []
    ) {
        throw new LogicException(
            'HRIS records are read-only. Decrementing is not allowed.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Model Information
    |--------------------------------------------------------------------------
    */

    public function isReadOnly(): bool
    {
        return true;
    }
}