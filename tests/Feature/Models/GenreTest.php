<?php

namespace Tests\Feature\Models;

use App\Models\Genre as Model;
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
        Model::factory()->create();
        $data = Model::all();
        $dataKeys = array_keys($data->first()->getAttributes());
        $this->assertCount(1, $data);

        $this->assertEqualsCanonicalizing([
            'uuid',
            'name',
            'description',
            'is_active',
            'created_at',
            'updated_at',
            'deleted_at'
        ], $dataKeys);
    }

    public function testCreatedIfName()
    {
        $data = Model::create(['name' => 'name']);
        $data->refresh();

        $this->assertEquals('name', $data->name);
        $this->assertNull($data->description);
        $this->assertTrue($data->is_active);
    }

    public function testCreatedIfDescription()
    {
        $data = Model::create(['name' => 'name', 'description' => null]);
        $data->refresh();

        $this->assertNull($data->description);

        $data = Model::create(['name' => 'name', 'description' => 'description']);
        $data->refresh();

        $this->assertEquals('description', $data->description);
    }

    public function testCreatedIfIsActive()
    {
        $data = Model::create(['name' => 'name', 'is_active' => false]);
        $data->refresh();

        $this->assertFalse($data->is_active);

        $data = Model::create(['name' => 'name', 'is_active' => true]);
        $data->refresh();

        $this->assertTrue($data->is_active);
    }
}
