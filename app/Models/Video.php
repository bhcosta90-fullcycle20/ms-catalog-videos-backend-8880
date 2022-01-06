<?php

namespace App\Models;

use App\Models\Traits\UploadFile;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Video extends Model
{
    use HasFactory, SoftDeletes, Traits\Uuid, UploadFile;

    const THUMB_FILE_MAX_SIZE = 1024 * 5; //5mb
    const BANNER_FILE_MAX_SIZE = 1024 * 10; //10mb
    const TRAILER_FILE_MAX_SIZE = 1024 * 1024 * 1; //1gb
    const VIDEO_FILE_MAX_SIZE = 1024 * 1024 * 50; //50gb

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
        'video_file',
        'thumb_file',
        'banner_file',
        'trailer_file',
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
        $files = self::extractFiles($attributes);
        try {
            DB::beginTransaction();
            /** @var self $obj */
            $obj = static::query()->create($attributes);
            $obj->handleRelations($attributes, 'attach');
            $obj->uploadFiles($files);
            DB::commit();
        } catch (Exception $e) {
            if (isset($obj)) {
                $obj->deleteFiles($files);
            }
            DB::rollBack();
            throw $e;
        }

        return $obj;
    }

    public function update(array $attributes = [], array $options = [])
    {
        $files = self::extractFiles($attributes);

        try {
            DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            $this->handleRelations($attributes);
            if ($saved) {
                $this->uploadFiles($files);
            }
            DB::commit();
            if ($saved && count($files)) {
                $this->deleteOldFiles();
            }
        } catch (Exception $e) {
            DB::rollBack();
            $this->deleteFiles($files);
            throw $e;
        }

        return $saved;
    }

    public function handleRelations($data, $method = 'sync'): self
    {
        if (isset($data['categories_id'])) {
            $this->categories()->$method(array_unique($data['categories_id']));
        }

        if (isset($data['genres_id'])) {
            $this->genres()->$method(array_unique($data['genres_id']));
        }
        return $this;
    }

    protected function fileDir(): string
    {
        return $this->id;
    }

    protected static function fileFields(): array
    {
        return [
            'video_file',
            'thumb_file',
            'banner_file',
            'trailer_file',
        ];
    }

    public function getThumbFileUrlAttribute()
    {
        return $this->thumb_file ? $this->getFileUrl($this->thumb_file) : null;
    }

    public function getBannerFileUrlAttribute()
    {
        return $this->banner_file ? $this->getFileUrl($this->banner_file) : null;
    }

    public function getTrailerFileUrlAttribute()
    {
        return $this->trailer_file ? $this->getFileUrl($this->trailer_file) : null;
    }

    public function getVideoFileUrlAttribute()
    {
        return $this->video_file ? $this->getFileUrl($this->video_file) : null;
    }
}
