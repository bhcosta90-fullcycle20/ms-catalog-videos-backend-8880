<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use App\Models\Genre as Model;
use Illuminate\Support\Facades\Lang;
use Tests\Traits\TestSave;
use Tests\Traits\TestValidation;

class GenreControllerTest extends TestCase
{
    use RefreshDatabase, TestValidation, TestSave;

    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = Model::factory()->create();
    }

    public function testIndex()
    {
        $category = $this->model;
        $response = $this->getJson('/genres');

        $response->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = $this->model;
        $response = $this->getJson('/genres/' . $category->id);

        $response->assertStatus(200)
            ->assertJson($category->toArray());
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
        $this->assertStore($data = [
            'name' => 'teste'
        ], $data + [
            'is_active' => true,
            'deleted_at' => null,
        ]);

        $this->assertStore($data = [
            'name' => 'teste',
            'is_active' => false,
        ], $data + [
            'is_active' => false,
        ]);
    }

    public function testUpdated()
    {
        $this->model = Model::factory()->create([
            'is_active' => false,
        ]);

        $response = $this->assertUpdate($data = [
            'name' => 'teste',
            'is_active' => true,
        ], $data + [
            'is_active' => true,
            'deleted_at' => null,
        ]);

        $response->assertJsonStructure([
            'created_at', 'updated_at',
        ]);
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
}
