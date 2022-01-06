<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Controllers\GenreController;
use App\Http\Resources\GenreResource;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Genre as Model;
use Illuminate\Http\Request;
use Mockery;
use Tests\Exceptions\TestException;
use Tests\Traits\TestResource;
use Tests\Traits\TestSave;
use Tests\Traits\TestValidation;

class GenreControllerTest extends TestCase
{
    use RefreshDatabase, TestValidation, TestSave, TestResource;

    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = Model::factory()->create();
    }

    public function testIndex()
    {
        $response = $this->getJson('/genres');

        $response->assertStatus(200)
            ->assertJson([$this->model->toArray()]);
    }

    public function testShow()
    {
        $response = $this->getJson('/genres/' . $this->model->id);

        $response->assertStatus(200);
        $this->assertResource($response, new GenreResource($this->model));
    }

    public function testCreatedInvalidationData()
    {
        $data = [
            'name' => '',
            'categories_id' => '',
        ];
        $this->assertInvalidationStore($data, 'required');
        $this->assertInvalidationUpdate($data, 'required');

        $data = [
            'name' => 'a',
        ];

        $this->assertInvalidationStore($data, 'min.string', ['min' => 3]);
        $this->assertInvalidationUpdate($data, 'min.string', ['min' => 3]);

        $data = [
            'name' => str_repeat('a', 500)
        ];

        $this->assertInvalidationStore($data, 'max.string', ['max' => 100]);
        $this->assertInvalidationUpdate($data, 'max.string', ['max' => 100]);


        $data = [
            'is_active' => 'a',
        ];

        $this->assertInvalidationStore($data, 'boolean');
        $this->assertInvalidationUpdate($data, 'boolean');

        $data = [
            'categories_id' => 'a',
        ];

        $this->assertInvalidationStore($data, 'array');
        $this->assertInvalidationUpdate($data, 'array');

        $data = [
            'categories_id' => ['a'],
        ];

        $this->assertInvalidationStore($data, 'exists');
        $this->assertInvalidationUpdate($data, 'exists');

        $category = Category::factory()->create();
        $category->delete();

        $data = [
            'categories_id' => [$category->id],
        ];

        $this->assertInvalidationStore($data, 'exists');
        $this->assertInvalidationUpdate($data, 'exists');
    }

    public function testRollbackStore()
    {
        /** @var $controller Mockery */
        $controller = Mockery::mock(GenreController::class);
        $controller->makePartial()->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn([
                'name' => 'teste',
            ]);


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
            $this->assertCount(1, Model::all());
        }
    }

    public function testRollbackUpdate()
    {
        /** @var $controller Mockery */
        $controller = Mockery::mock(GenreController::class);
        $controller->makePartial()->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn([
                'name' => 'teste',
            ]);

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
            $this->assertCount(1, Model::all());
        }
    }

    public function testCreated()
    {
        $category = Category::factory()->create();

        $data = [
            'name' => 'teste',
        ];

        $response = $this->assertStore($data + ['categories_id' => [$category->id]], $data + [
            'is_active' => true,
            'deleted_at' => null,
        ]);
        $this->assertHasCategory($response->json('id'), $category->id);

        $response = $this->assertStore(($data = [
            'name' => 'teste',
            'is_active' => false,
        ]) + ['categories_id' => [$category->id]], $data + [
            'is_active' => false,
        ]);
        $this->assertHasCategory($response->json('id'), $category->id);
    }

    public function testUpdated()
    {
        $category = Category::factory()->create();

        $this->model = Model::factory()->create([
            'is_active' => false,
        ]);

        $data = [
            'name' => 'teste',
            'is_active' => true,
        ];

        $response = $this->assertUpdate($data + ['categories_id' => [$category->id]], $data + [
            'is_active' => true,
            'deleted_at' => null,
        ]);

        $response->assertJsonStructure([
            'created_at', 'updated_at',
        ]);
        $this->assertHasCategory($response->json('id'), $category->id);
    }

    public function testDestroy()
    {
        $objUpdate = $this->model;
        $this->deleteJson($this->routePut())
            ->assertStatus(204);

        $this->assertNull(Model::find($objUpdate->id));
        $this->assertNotNull(Model::withTrashed()->find($objUpdate->id));
    }

    public function testSyncCategories()
    {
        $categories = Category::factory(3)->create()->pluck('id')->toArray();

        $sendData = [
            'name' => 'teste'
        ];

        $response = $this->postJson($this->routeStore(), $sendData + ['categories_id' => [$categories[0]]]);
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categories[0],
            'genre_id' => $idGenre = $response->json('id') ?: $response->json('data.id'),
        ]);

        $response = $this->putJson('genres/' . $idGenre, $sendData + [
            'categories_id' => [$categories[1], $categories[2]]
        ]);
        
        $this->assertDatabaseMissing('category_genre', [
            'category_id' => $categories[0],
            'genre_id' => $idGenre,
        ]);

        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categories[1],
            'genre_id' => $idGenre,
        ]);
        
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categories[2],
            'genre_id' => $idGenre,
        ]);
    }

    protected function routeStore()
    {
        return '/genres';
    }

    protected function routePut()
    {
        return '/genres/' . $this->model->id;
    }

    protected function model()
    {
        return new Model;
    }

    protected function assertHasCategory($idGenre, $idCategory)
    {
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $idCategory,
            'genre_id' => $idGenre,
        ]);
    }
}
