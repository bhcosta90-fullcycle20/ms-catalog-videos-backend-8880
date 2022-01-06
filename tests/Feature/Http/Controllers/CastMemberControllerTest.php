<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Resources\CastMemberResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\CastMember as Model;
use Tests\Traits\TestResource;
use Tests\Traits\TestSave;
use Tests\Traits\TestValidation;

class CastMemberControllerTest extends TestCase
{
    use RefreshDatabase, TestValidation, TestSave, TestResource;

    private Model $model;

    private array $serializeFields = [
        'id',
        'name',
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
        $response = $this->getJson('/cast_members');

        $response->assertStatus(200)
            ->assertJson([
                'meta' => ['per_page' => 15]
            ])
            ->assertJsonStructure([
                'data' => ['*' => $this->serializeFields],
                'links' => [],
                'meta' => [],
            ]);

        $resource = CastMemberResource::collection([$this->model]);
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this->getJson('/cast_members/' . $this->model->id);

        $response->assertStatus(200);
        $this->assertResource($response, new CastMemberResource($this->model));
    }

    public function testCreatedInvalidationData()
    {
        $data = [
            'name' => '',
            'type' => '',
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
            'type' => 'a',
        ];

        $this->assertInvalidationStore($data, 'in');
        $this->assertInvalidationUpdate($data, 'in');
    }

    public function testCreated()
    {
        $datas = [
            [
                'name' => 'teste',
                'type' => Model::TYPE_ACTOR,
            ],
            [
                'name' => 'teste',
                'type' => Model::TYPE_DIRECTOR,
            ]
        ];
        foreach($datas as $data)
        {
            $response = $this->assertStore($data, $data + [
                'deleted_at' => null,
            ]);

            $response->assertJsonStructure(['data' => $this->serializeFields]);
            $this->assertResource($response, new CastMemberResource(Model::find($this->getIdFromResponse($response))));
        }
    }

    public function testUpdated()
    {
        $this->model = Model::factory()->create([
            'type' => Model::TYPE_DIRECTOR,
        ]);

        $response = $this->assertUpdate($data = [
            'name' => 'teste',
            'type' => Model::TYPE_ACTOR,
        ], $data + [
            'type' => Model::TYPE_ACTOR,
            'deleted_at' => null,
        ]);

        $response->assertJsonStructure(['data' => $this->serializeFields]);
        $this->assertResource($response, new CastMemberResource(Model::find($this->getIdFromResponse($response))));
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
        return '/cast_members';
    }

    protected function routePut()
    {
        return '/cast_members/' . $this->model->id;
    }

    protected function model()
    {
        return new Model;
    }    
}
