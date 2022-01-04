<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use App\Models\Genre as Model;
use Illuminate\Support\Facades\Lang;
use Tests\Traits\TestValidation;

class GenreControllerTest extends TestCase
{
    use RefreshDatabase, TestValidation;

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
        $response = $this->postJson($this->routeStore(), [
            'name' => 'teste',
        ])->assertStatus(201);

        $obj = Model::find($response->json('id') ?: $response->json('data.id'));
        $response->assertJson($obj->toArray());
        $this->assertTrue($response->json('is_active'));

        $response = $this->postJson($this->routeStore(), [
            'name' => 'teste',
            'is_active' => false,
        ])->assertStatus(201)
            ->assertJsonFragment([
                'is_active' => false,
            ]);
    }

    public function testUpdated()
    {
        $this->putJson($this->routePut(), [
            'name' => 'teste',
            'is_active' => true,
        ])->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'teste',
                'is_active' => true,
            ]);

        $this->putJson($this->routePut(), [
            'name' => 'teste',
            'is_active' => false,
        ])->assertStatus(200)
            ->assertJsonFragment([
                'is_active' => false,
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
}
