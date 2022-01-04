<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Category as Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Lang;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndex()
    {
        $category = Model::factory()->create();
        $response = $this->getJson('/categories');

        $response->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = Model::factory()->create();
        $response = $this->getJson('/categories/' . $category->id);

        $response->assertStatus(200)
            ->assertJson($category->toArray());
    }

    public function testCreatedInvalidationData()
    {
        $response = $this->postJson('/categories');

        $this->assertInvalidationRequired($response);

        $response = $this->postJson('/categories', [
            'name' => 'a'
        ]);

        $this->assertInvalidationMin($response);

        $response = $this->postJson('/categories', [
            'name' => str_repeat('a', 500)
        ]);
        $this->assertInvalidationMax($response);

        $response = $this->postJson('/categories', [
            'is_active' => 'a',
        ]);

        $this->assertInvalidationBoolean($response);
    }

    public function testCreated()
    {
        $response = $this->postJson('/categories', [
            'name' => 'teste',
        ])->assertStatus(201);

        $obj = Model::find($response->json('id') ?: $response->json('data.id'));
        $response->assertJson($obj->toArray());
        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));

        $response = $this->postJson('/categories', [
            'name' => 'teste',
            'is_active' => false,
            'description' => 'teste',
        ])->assertStatus(201)
            ->assertJsonFragment([
                'is_active' => false,
                'description' => 'teste',
            ]);
    }

    public function testUpdated()
    {
        $objUpdate = Model::factory()->create([
            'is_active' => false
        ]);

        $this->putJson('/categories/' . $objUpdate->id, [
            'name' => 'teste',
            'is_active' => true,
            'description' => 'teste',
        ])->assertStatus(200)
        ->assertJsonFragment([
            'name' => 'teste',
            'description' => 'teste',
            'is_active' => true,
        ]);

        $this->putJson('/categories/' . $objUpdate->id, [
            'name' => 'teste',
            'is_active' => false,
            'description' => '',
        ])->assertStatus(200)
        ->assertJsonFragment([
            'is_active' => false,
            'description' => null,
        ]);
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
