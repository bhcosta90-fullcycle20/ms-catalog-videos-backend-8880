<?php

namespace Tests\Feature\Http\Controllers\Video;

use App\Http\Resources\VideoResource;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video as Model;
use Tests\Traits\TestResource;
use Tests\Traits\TestSave;
use Tests\Traits\TestUploads;

class VideoControllerCrudTest extends BaseVideoControllerAbstract
{
    use TestSave, TestUploads, TestResource;

    public function testIndex()
    {
        $category = $this->model;
        $response = $this->getJson('/videos');

        $response->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $response = $this->getJson('/videos/' . $this->model->id);

        $response->assertStatus(200);
        $this->assertResource($response, new VideoResource($this->model));
    }

    public function testCreatedAndUpdate()
    {
        $category = Category::factory()->create();
        $genre = Genre::factory()->create();
        $genre->categories()->sync($category);

        $datas = [
            [
                'send_data' => $this->sendData,
                'test_data' => $this->sendData + ['opened' => false]
            ],
            [
                'send_data' => $this->sendData + ['opened' => true],
                'test_data' => $this->sendData + ['opened' => true]
            ],
            [
                'send_data' => $this->sendData + ['rating' => Model::RATINGS[3]],
                'test_data' => $this->sendData + ['rating' => Model::RATINGS[3]]
            ]
        ];

        foreach ($datas as $data) {
            $data['send_data']['categories_id'] = [$category->id];
            $data['send_data']['genres_id'] = [$genre->id];

            $response = $this->assertStore($data['send_data'], $data['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['data' => $this->serializeFields]);
            $this->assertHasCategory($id = $this->getIdFromResponse($response), $data['send_data']['categories_id'][0]);
            $this->assertHasGenre($id, $data['send_data']['genres_id'][0]);

            $response = $this->assertUpdate($data['send_data'], $data['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['data' => $this->serializeFields]);
            $this->assertHasCategory($id = $this->getIdFromResponse($response), $data['send_data']['categories_id'][0]);
            $this->assertHasGenre($id, $data['send_data']['genres_id'][0]);
        }
    }

    public function testDestroy()
    {
        $objUpdate = $this->model;
        $this->deleteJson($this->routePut())
            ->assertStatus(204);

        $this->assertNull(Model::find($objUpdate->id));
        $this->assertNotNull(Model::withTrashed()->find($objUpdate->id));
    }

    protected function assertHasCategory($idVideo, $idCategory)
    {
        $this->assertDatabaseHas('category_video', [
            'category_id' => $idCategory,
            'video_id' => $idVideo,
        ]);
    }

    protected function assertHasGenre($idVideo, $idGenre)
    {
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $idGenre,
            'video_id' => $idVideo,
        ]);
    }
}
