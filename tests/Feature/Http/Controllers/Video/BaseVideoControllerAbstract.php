<?php

namespace Tests\Feature\Http\Controllers\Video;

use Tests\TestCase;
use App\Models\Video as Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class BaseVideoControllerAbstract extends TestCase
{
    use RefreshDatabase;
    
    protected Model $model;

    protected $sendData = [];

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->model = Model::factory()->create([
            'opened' => false
        ]);

        $this->sendData = [
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 1990,
            'rating' => Model::RATINGS[0],
            'duration' => 60,
        ];
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