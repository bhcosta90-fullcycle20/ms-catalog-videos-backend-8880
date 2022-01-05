<?php

namespace Tests\Feature\Models;

use App\Models\Video as Model;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VideoTest extends TestCase
{
    use RefreshDatabase;

    private Model $model;

    private $sendData = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->sendData = [
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 1990,
            'rating' => Model::RATINGS[0],
            'duration' => 60,
        ];
    }

    public function testRollbackStore()
    {
        try {
            Model::create($this->sendData + ['categories_id' => [1]]);
        } catch(QueryException $e) {
            $this->assertEquals(23000, $e->getCode());
            $this->assertEquals(0, Model::count());
        }
    }

    public function testRollbackUpdate()
    {
        $objModel = Model::factory()->create();
        $videoTitle = $objModel->title;

        try {
            $objModel->update($this->sendData + ['categories_id' => [1]]);
        } catch (QueryException $e) {
            $this->assertDatabaseHas('videos', [
                'id' => $objModel->id,
                'title' => $videoTitle,
            ]);
            $this->assertEquals(23000, $e->getCode());
        }
    }
}
