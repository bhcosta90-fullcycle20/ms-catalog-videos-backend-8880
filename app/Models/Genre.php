<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Genre extends Model
{
    use HasFactory, SoftDeletes, Traits\Uuid;

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'id' => 'string',
        'is_active' => 'boolean'
    ];

    public function videos()
    {
        return $this->belongsToMany(Video::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
