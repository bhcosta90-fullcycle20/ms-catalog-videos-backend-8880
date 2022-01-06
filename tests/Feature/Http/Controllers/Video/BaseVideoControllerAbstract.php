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