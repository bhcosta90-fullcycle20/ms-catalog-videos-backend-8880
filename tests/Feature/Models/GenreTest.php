<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testList()
    {
        Genre::factory()->create();
        $genres = Genre::all();
        $genresKeys = array_keys($genres->first()->getAttributes());
        $this->assertCount(1, $genres);

        $this->assertEqualsCanonicalizing([
            'uuid',
            'name',
            'description',
            'is_active',
            'created_at',
            'updated_at',
            'deleted_at'
        ], $genresKeys);
    }
}
