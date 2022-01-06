<?php

namespace Tests\Feature\Http\Controllers\Video;

use App\Models\Video as Model;
use Tests\Feature\Models\Video\BaseVideo;

abstract class BaseVideoControllerAbstract extends BaseVideo
{
    protected Model $model;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->model = Model::factory()->create([
            'opened' => false
        ]);
    }
    
    protected array $serializeFields = [
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
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function routeStore()
    {
        return '/videos';
    }

    protected function routePut()
    {
        return '/videos/' . $this->model->id;
    }

    protected function model()
    {
        return new Model;
    }
}