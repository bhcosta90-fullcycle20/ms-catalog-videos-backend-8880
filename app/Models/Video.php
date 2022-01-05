<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Video extends Model
{
    use HasFactory, SoftDeletes, Traits\Uuid;

    const RATINGS = [
        'L',
        '10',
        '12',
        '14',
        '16',
        '18',
    ];

    protected $fillable = [
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration',
    ];

    protected $casts = [
        'id' => 'string',
        'year_launched' => 'integer',
        'opened' => 'boolean',
        'duration' => 'integer',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class)->withTrashed();
    }



    public static function create(array $attributes = [])
    {
        try {
            DB::beginTransaction();
            $obj = static::query()->create($attributes);
            $obj->handleRelations($attributes, 'attach');
            // Uploads
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $obj;
    }

    public function update(array $attributes = [], array $options = [])
    {
        try {
            DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            $this->handleRelations($attributes);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $saved;
    }

    protected function handleRelations($data, $method = 'sync'): self
    {
        $this->categories()->$method(array_unique($data['categories_id'] ?? []));
        $this->genres()->$method(array_unique($data['genres_id'] ?? []));
        return $this;
    }
}
