<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait Uuid
{
    public static function bootUuid(): void
    {
        static::creating(function ($obj) {
            $obj->id = Str::uuid();
        });
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }
}
