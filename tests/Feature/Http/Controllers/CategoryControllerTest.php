<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category as Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestResource;
use Tests\Traits\TestSave;
use Tests\Traits\TestValidation;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase, TestValidation, TestSave, TestResource;

    private Model $model;

    private $serializeFields = [
        'id',
        'name',
        'description',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = Model::factory()->create();
    }

    public function testIndex()
    {
        $response = $this->getJson('/categories');
        $response->assertStatus(200)
            ->assertJson([
                'meta' => ['per_page' => 15]
            ])
            ->assertJsonStructure([
                'data' => ['*' => $this->serializeFields],
                'links' => [],
                'meta' => [],
            ]);
        $resource = CategoryResource::collection([$this->model]);
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $category = $this->model;
        $response = $this->getJson('/categories/' . $category->id);

        $resource = new CategoryResource(Model::find($this->getIdFromResponse($response)));
        $this->assertResource($response, $resource);
    }

    public function testCreatedInvalidationData()
    {
        $data = [
            'name' => '',
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
    }

    public function testCreated()
    {
        $response = $this->assertStore($data = [
            'name' => 'teste'
        ], $data + [
            'is_active' => true,
            'description' => null,
            'deleted_at' => null,
        ]);
        $response->assertJsonStructure(['data' => $this->serializeFields]);

        $response = $this->assertStore($data = [
            'name' => 'teste',
            'is_active' => false,
            'description' => 'teste',
        ], $data + [
            'is_active' => false,
            'description' => 'teste',
        ]);

        $id = $this->getIdFromResponse($response);
        $resource = new CategoryResource(Model::find($id));
        $this->assertResource($response, $resource);
    }

    public function testUpdated()
    {
        $this->model = Model::factory()->create([
            'description' => 'description',
            'is_active' => false,
        ]);

        $response = $this->assertUpdate($data = [
            'name' => 'teste',
            'description' => 'teste',
            'is_active' => true,
        ], $data + [
            'is_active' => true,
            'description' => 'teste',
            'deleted_at' => null,
        ]);

        $response->assertJsonStructure(['data' => $this->serializeFields]);
        $resource = new CategoryResource(Model::find($this->getIdFromResponse($response)));
        $this->assertResource($response, $resource);

        $data['description'] = '';
        $response = $this->assertUpdate($data, ['description' => null]);

        $data['description'] = 'teste';
        $response = $this->assertUpdate($data, ['description' => 'teste']);
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
        return '/categories';
    }

    protected function routePut()
    {
        $obj = $this->model;
        return '/categories/' . $obj->id;
    }

    protected function model()
    {
        return new Model;
    }
}
