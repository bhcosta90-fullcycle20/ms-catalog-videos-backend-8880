<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastMember extends Model
{
    use HasFactory, Traits\Uuid, SoftDeletes;

    const TYPE_DIRECTOR = 1;
    const TYPE_ACTOR = 2;

    const TYPES = [
        self::TYPE_DIRECTOR => 'Director',
        self::TYPE_ACTOR => 'Actor',
    ];

    protected $fillable = [
        'name',
        'is_active',
        'type',
    ];

    protected $casts = [
        'id' => 'string',
        'is_active' => 'boolean',
        'type' => 'integer'
    ];
}
