<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Video as Model;
use Tests\Traits\TestSave;
use Tests\Traits\TestValidation;

class VideoControllerTest extends TestCase
{
    use RefreshDatabase, TestValidation, TestSave;

    private Model $model;

    private $sendData = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = Model::factory()->create();

        $this->sendData = [
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 1990,
            'rating' => Model::RATINGS[0],
            'duration' => 60
        ];
    }

    public function testIndex()
    {
        $category = $this->model;
        $response = $this->getJson('/videos');

        $response->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = $this->model;
        $response = $this->getJson('/videos/' . $category->id);

        $response->assertStatus(200)
            ->assertJson($category->toArray());
    }

    public function testInvalidateRequired()
    {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
        ];

        $this->assertInvalidationStore($data, "required");
        $this->assertInvalidationUpdate($data, "required");
    }

    public function testInvalidateMinString()
    {
        $data = [
            'title' => 'a',
        ];

        $this->assertInvalidationStore($data, "min.string", ['min' => 3]);
        $this->assertInvalidationUpdate($data, "min.string", ['min' => 3]);
    }

    public function testInvalidateMaxString()
    {
        $data = [
            'title' => str_repeat('a', 500),
        ];

        $this->assertInvalidationStore($data, "max.string", ['max' => 100]);
        $this->assertInvalidationUpdate($data, "max.string", ['max' => 100]);
    }

    public function testInvalidateInteger()
    {
        $data = [
            'duration' => 'a',
        ];

        $this->assertInvalidationStore($data, "integer");
        $this->assertInvalidationUpdate($data, "integer");
    }

    public function testInvalidateBoolean()
    {
        $data = [
            'opened' => 'a',
        ];

        $this->assertInvalidationStore($data, "boolean");
        $this->assertInvalidationUpdate($data, "boolean");
    }

    public function testInvalidateDateFormatYear()
    {
        $data = [
            'year_launched' => 'a',
        ];

        $this->assertInvalidationStore($data, "date_format", ['format' => 'Y']);
        $this->assertInvalidationUpdate($data, "date_format", ['format' => 'Y']);
    }

    public function testInvalidateIn()
    {
        $data = [
            'rating' => 'a',
        ];

        $this->assertInvalidationStore($data, "in");
        $this->assertInvalidationUpdate($data, "in");
    }

    public function testCreated()
    {
        $response = $this->assertStore($this->sendData, $this->sendData + [
            'opened' => false,
        ]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $response = $this->assertStore([
            'opened' => true
        ] + $this->sendData, [
            'opened' => true,
        ] + $this->sendData);

        $response = $this->assertStore([
            'rating' => Model::RATINGS[1]
        ] + $this->sendData, [
            'rating' => Model::RATINGS[1],
        ] + $this->sendData);
    }

    // public function testUpdated()
    // {
    //     $this->model = Model::factory()->create([
    //         'type' => CastMember::TYPE_DIRECTOR,
    //     ]);

    //     $response = $this->assertUpdate($data = [
    //         'name' => 'teste',
    //         'type' => CastMember::TYPE_ACTOR,
    //     ], $data + [
    //         'type' => CastMember::TYPE_ACTOR,
    //         'deleted_at' => null,
    //     ]);

    //     $response->assertJsonStructure([
    //         'created_at', 'updated_at',
    //     ]);
    // }

    // public function testDestroy()
    // {
    //     $objUpdate = $this->model;
    //     $this->deleteJson($this->routePut())
    //         ->assertStatus(204);

    //     $this->assertNull(Model::find($objUpdate->id));
    //     $this->assertNotNull(Model::withTrashed()->find($objUpdate->id));
    // }

    protected function routeStore()
    {
        return '/videos';
    }

    protected function routePut()
    {
        return '/videos/' . $this->model->id;
    }

    protected function model()
    {
        return new Model;
    }
}
