<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Controllers\VideoController;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Video as Model;
use App\Models\Video;
use Exception;
use Illuminate\Http\Request;
use Mockery;
use Tests\Exceptions\TestException;
use Tests\Traits\TestSave;
use Tests\Traits\TestValidation;

class VideoControllerTest extends TestCase
{
    use RefreshDatabase, TestValidation, TestSave;

    private Model $model;

    private $sendData = [];

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

    public function testIndex()
    {
        $category = $this->model;
        $response = $this->getJson('/videos');

        $response->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = $this->model;
        $response = $this->getJson('/videos/' . $category->id);

        $response->assertStatus(200)
            ->assertJson($category->toArray());
    }

    public function testInvalidateRequired()
    {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
            'categories_id' => '',
            'genres_id' => '',
        ];

        $this->assertInvalidationStore($data, "required");
        $this->assertInvalidationUpdate($data, "required");
    }

    public function testInvalidateMinString()
    {
        $data = [
            'title' => 'a',
        ];

        $this->assertInvalidationStore($data, "min.string", ['min' => 3]);
        $this->assertInvalidationUpdate($data, "min.string", ['min' => 3]);
    }

    public function testInvalidateMaxString()
    {
        $data = [
            'title' => str_repeat('a', 500),
        ];

        $this->assertInvalidationStore($data, "max.string", ['max' => 100]);
        $this->assertInvalidationUpdate($data, "max.string", ['max' => 100]);
    }

    public function testInvalidateInteger()
    {
        $data = [
            'duration' => 'a',
        ];

        $this->assertInvalidationStore($data, "integer");
        $this->assertInvalidationUpdate($data, "integer");
    }

    public function testInvalidateBoolean()
    {
        $data = [
            'opened' => 'a',
        ];

        $this->assertInvalidationStore($data, "boolean");
        $this->assertInvalidationUpdate($data, "boolean");
    }

    public function testInvalidateDateFormatYear()
    {
        $data = [
            'year_launched' => 'a',
        ];

        $this->assertInvalidationStore($data, "date_format", ['format' => 'Y']);
        $this->assertInvalidationUpdate($data, "date_format", ['format' => 'Y']);
    }

    public function testInvalidateIn()
    {
        $data = [
            'rating' => 'a',
        ];

        $this->assertInvalidationStore($data, "in");
        $this->assertInvalidationUpdate($data, "in");
    }

    public function testInvalidateArray()
    {
        $data = [
            'categories_id' => '123',
            'genres_id' => '123',
        ];

        $this->assertInvalidationStore($data, "array");
        $this->assertInvalidationUpdate($data, "array");
    }

    public function testInvalidateExists()
    {
        $data = [
            'categories_id' => ['123'],
            'genres_id' => ['123'],
        ];

        $this->assertInvalidationStore($data, "exists");
        $this->assertInvalidationUpdate($data, "exists");

        $category = Category::factory()->create();
        $category->delete();
        $genre = Genre::factory()->create();
        $genre->delete();

        $data = [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id],
        ];

        $this->assertInvalidationStore($data, "exists");
        $this->assertInvalidationUpdate($data, "exists");
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
            $response->assertJsonStructure(['created_at', 'updated_at']);
            $this->assertHasCategory($response->json('id'), $data['send_data']['categories_id'][0]);
            $this->assertHasGenre($response->json('id'), $data['send_data']['genres_id'][0]);

            $response = $this->assertUpdate($data['send_data'], $data['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
            $this->assertHasCategory($response->json('id'), $data['send_data']['categories_id'][0]);
            $this->assertHasGenre($response->json('id'), $data['send_data']['genres_id'][0]);
        }
    }

    public function testSyncCategories()
    {
        $categories = Category::factory(3)->create()->pluck('id')->toArray();
        $genre = Genre::factory(1)->create()->each(fn ($genre) => $genre->categories()->attach($categories))->first();

        $response = $this->postJson($this->routeStore(), $this->sendData + [
            'categories_id' => [$categories[0]],
            'genres_id' => [$genre->id]
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categories[0],
            'video_id' => $response->json('id') ?: $response->json('data.id'),
        ]);

        $response = $this->putJson($this->routePut(), $this->sendData + [
            'categories_id' => [$categories[1], $categories[2]],
            'genres_id' => [$genre->id]
        ]);

        $this->assertDatabaseMissing('category_video', [
            'category_id' => $categories[0],
            'video_id' => $response->json('id') ?: $response->json('data.id'),
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categories[1],
            'video_id' => $response->json('id') ?: $response->json('data.id'),
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categories[2],
            'video_id' => $response->json('id') ?: $response->json('data.id'),
        ]);
    }

    public function testSyncGenres()
    {
        $category = Category::factory()->create();
        $genres = Genre::factory(3)->create()->each(fn($genre) => $genre->categories()->attach($category));
        
        $genresId = $genres->pluck('id')->toArray();

        $response = $this->postJson($this->routeStore(), $this->sendData + [
            'categories_id' => [$category->id],
            'genres_id' => [$genresId[0]]
        ]);

        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[0],
            'video_id' => $idVideo = $response->json('id') ?: $response->json('data.id'),
        ]);

        $response = $this->putJson('videos/' . $idVideo, $this->sendData + [
            'categories_id' => [$category->id],
            'genres_id' => [$genresId[1], $genresId[2]]
        ]);

        $this->assertDatabaseMissing('genre_video', [
            'genre_id' => $genresId[0],
            'video_id' => $idVideo,
        ]);

        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[1],
            'video_id' => $idVideo,
        ]);

        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[2],
            'video_id' => $idVideo,
        ]);
    }

    public function testRollbackStore()
    {
        /** @var $controller Mockery */
        $controller = Mockery::mock(VideoController::class);
        $controller->makePartial()->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);

        $controller->shouldReceive('ruleStore')
            ->withAnyArgs()
            ->andReturn([]);

        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException('errorHandleRelations'));

        /** @var $request Mockery */
        $request = Mockery::mock(Request::class);

        try {
            $controller->store($request);
        } catch (TestException $e) {
            $this->assertEquals('errorHandleRelations', $e->getMessage());
            $this->assertCount(1, Video::all());
        }
    }

    public function testRollbackUpdate()
    {
        /** @var $controller Mockery */
        $controller = Mockery::mock(VideoController::class);
        $controller->makePartial()->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);

        $controller->shouldReceive('ruleStore')
            ->withAnyArgs()
            ->andReturn([]);

        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException('errorHandleRelations'));

        /** @var $request Mockery */
        $request = Mockery::mock(Request::class);

        try {
            $controller->update($request, $this->model->id);
        } catch (TestException $e) {
            $this->assertEquals('errorHandleRelations', $e->getMessage());
            $this->assertCount(1, Video::all());
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
