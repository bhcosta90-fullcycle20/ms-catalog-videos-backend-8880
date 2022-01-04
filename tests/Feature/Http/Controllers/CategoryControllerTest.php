<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndex()
    {
        $category = Category::factory()->create();
        $response = $this->getJson('/categories');

        $response->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = Category::factory()->create();
        $response = $this->getJson('/categories/' . $category->id);

        $response->assertStatus(200)
            ->assertJson($category->toArray());
    }
}
