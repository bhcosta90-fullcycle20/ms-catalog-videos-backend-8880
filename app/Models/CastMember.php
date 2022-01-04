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
        self::TYPE_DIRECTOR,
        self::TYPE_ACTOR,
    ];

    protected $fillable = [
        'name',
        'type',
    ];

    protected $casts = [
        'id' => 'string',
        'type' => 'integer'
    ];
}
