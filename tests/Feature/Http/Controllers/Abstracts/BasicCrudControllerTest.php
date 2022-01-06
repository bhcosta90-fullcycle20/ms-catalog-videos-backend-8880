<?php

namespace Tests\Feature\Http\Controllers\Abstracts;

use App\Http\Controllers\Abstracts\BasicCrudController;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mockery;
use ReflectionClass;
use Tests\Stubs\Controllers\CategoryControllerStub;
use Tests\Stubs\Models\CategoryStub;
use Tests\Stubs\Resources\CategoryResourceStub;
use Tests\TestCase;

class BasicCrudControllerTest extends TestCase
{
    private CategoryControllerStub $controller;
    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new CategoryControllerStub;

        CategoryStub::dropTable();
        CategoryStub::createTable();
    }

    protected function tearDown(): void
    {
        CategoryStub::dropTable();
        parent::tearDown();
    }

    public function testIndex()
    {
        $category = CategoryStub::create(['name' => 'teste', 'description' => 'teste']);
        $result = $this->controller->index();
        $resource = CategoryResourceStub::collection([$category]);
        $this->assertEquals($result->response()->getData(true)['data'], $resource->response()->getData(true)['data']);
    }

    public function testInvalidationStore()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        /** @var $mockery Mockery */
        $mockery = Mockery::mock(Request::class);
        $mockery->shouldReceive('all')
            ->once()
            ->andReturn(['name' => '']);

        /** @var Request $request */
        $request = $mockery;
        $this->controller->store($request);
    }

    public function testStore()
    {
        /** @var $mockery Mockery */
        $mockery = Mockery::mock(Request::class);
        $mockery->shouldReceive('all')
            ->once()
            ->andReturn(['name' => 'test_name']);

        /** @var Request $request */
        $request = $mockery;
        $result = $this->controller->store($request);

        $this->assertEquals($result->response()->getData(true), ['data' => CategoryStub::find(1)->toArray()]);
    }

    public function testShow()
    {
        $category = CategoryStub::create(['name' => 'teste', 'description' => 'teste']);
        /** @var JsonResource $result */
        $result = $this->controller->show($category->id);
        $this->assertEquals($result->response()->getData(true), ['data' => $category->toArray()]);
    }

    public function testUpdate()
    {
        $category = CategoryStub::create(['name' => 'teste', 'description' => 'teste']);

        /** @var $mockery Mockery */
        $mockery = Mockery::mock(Request::class);
        $mockery->shouldReceive('all')
        ->once()
            ->andReturn(['name' => 'test_name']);

        /** @var Request $request */
        $request = $mockery;
        $result = $this->controller->update($request, $category->id);
        $categoryUpdate = ['name' => 'test_name'] + $category->toArray();
        $this->assertEquals($result->response()->getData(true), ['data' => $categoryUpdate]);
    }

    public function testDestroy()
    {
        $category = CategoryStub::create(['name' => 'teste', 'description' => 'teste']);

        $response = $this->controller->destroy($category->id);
        $this->assertCount(0, CategoryStub::all());

        $this->createTestResponse($response)
            ->assertStatus(204);
    }

    public function testIfFindOrFailFetchModel()
    {
        $category = CategoryStub::create(['name' => 'teste', 'description' => 'teste']);

        $reflectionClass = new ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs($this->controller, [$category->id]);
        $this->assertInstanceOf(CategoryStub::class, $result);
    }

    public function testIfFindOrFailThrowExceptionWhenIdInvalid()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $reflectionClass = new ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs($this->controller, [0]);
        $this->assertInstanceOf(CategoryStub::class, $result);
    }
}
