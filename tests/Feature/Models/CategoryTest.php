<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testList()
    {
        Category::factory()->create();
        $categories = Category::all();
        $categoryKeys = array_keys($categories->first()->getAttributes());
        $this->assertCount(1, $categories);

        $this->assertEqualsCanonicalizing([
            'uuid',
            'name',
            'description',
            'is_active',
            'created_at',
            'updated_at',
            'deleted_at'
        ], $categoryKeys);
    }
}
