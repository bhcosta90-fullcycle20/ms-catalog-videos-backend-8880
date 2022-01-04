<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use App\Models\Genre as Model;
use Illuminate\Support\Facades\Lang;

class GenreControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndex()
    {
        $category = Model::factory()->create();
        $response = $this->getJson('/genres');

        $response->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = Model::factory()->create();
        $response = $this->getJson('/genres/' . $category->id);

        $response->assertStatus(200)
            ->assertJson($category->toArray());
    }

    public function testCreatedInvalidationData()
    {
        $response = $this->postJson('/genres');

        $this->assertInvalidationRequired($response);

        $response = $this->postJson('/genres', [
            'name' => 'a'
        ]);

        $this->assertInvalidationMin($response);

        $response = $this->postJson('/genres', [
            'name' => str_repeat('a', 500)
        ]);
        $this->assertInvalidationMax($response);

        $response = $this->postJson('/genres', [
            'is_active' => 'a',
        ]);

        $this->assertInvalidationBoolean($response);
    }

    public function testCreated()
    {
        $response = $this->postJson('/genres', [
            'name' => 'teste',
        ])->assertStatus(201);

        $obj = Model::find($response->json('id') ?: $response->json('data.id'));
        $response->assertJson($obj->toArray());
        $this->assertTrue($response->json('is_active'));

        $response = $this->postJson('/genres', [
            'name' => 'teste',
            'is_active' => false,
        ])->assertStatus(201)
            ->assertJsonFragment([
                'is_active' => false,
            ]);
    }

    public function testUpdated()
    {
        $objUpdate = Model::factory()->create([
            'is_active' => false
        ]);

        $this->putJson('/genres/' . $objUpdate->id, [
            'name' => 'teste',
            'is_active' => true,
        ])->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'teste',
                'is_active' => true,
            ]);

        $this->putJson('/genres/' . $objUpdate->id, [
            'name' => 'teste',
            'is_active' => false,
        ])->assertStatus(200)
            ->assertJsonFragment([
                'is_active' => false,
            ]);
    }

    public function testDestroy()
    {
        $objUpdate = Model::factory()->create();
        $this->deleteJson('/genres/' . $objUpdate->id)
            ->assertStatus(204);

        $this->assertNull(Model::find($objUpdate->id));
        $this->assertNotNull(Model::withTrashed()->find($objUpdate->id));
    }

    protected function assertInvalidationRequired(TestResponse $response)
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment([Lang::get("validation.required", ['attribute' => 'name'])]);
    }

    protected function assertInvalidationMin(TestResponse $response)
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([Lang::get("validation.min.string", ['attribute' => 'name', 'min' => 3])]);
    }

    protected function assertInvalidationMax(TestResponse $response)
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([Lang::get("validation.max.string", ['attribute' => 'name', 'max' => 100])]);
    }

    protected function assertInvalidationBoolean(TestResponse $response)
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment([Lang::get("validation.boolean", ['attribute' => 'is active'])]);
    }
}
