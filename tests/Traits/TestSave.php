<?php

declare(strict_types=1);

namespace Tests\Traits;

use Exception;
use Illuminate\Testing\TestResponse;

trait TestSave
{
    protected function assertStore(
        array $sendData,
        array $testData,
        array $testJsonData = null
    ): TestResponse {
        /** @var TestResponse $response */
        $response = $this->postJson($this->routeStore(), $sendData);

        if ($response->status() !== 201) {
            throw new Exception("Response status must be 201, given: {$response->status()}: {$response->content()}");
        }

        $id = $response->json('data.id') ?: $response->json('id');

        $model = new $this->model();
        $keyName = $model->getKeyName();
        $testData += [
            $keyName => $id,
        ];

        $this->assertDatabaseHas($model->getTable(), $testData);

        $testResponse = $testJsonData ?: $testData;
        $response->assertJsonFragment($testResponse);

        return $response;
    }

    protected function assertUpdate(
        array $sendData,
        array $testData,
        array $testJsonData = null
    ): TestResponse {
        /** @var TestResponse $response */
        $response = $this->putJson($this->routePut(), $sendData);

        if (!in_array($response->status(), [200, 201])) {
            throw new Exception("Response status must be 200 or 201, given: {$response->status()}: {$response->content()}");
        }

        $id = $response->json('data.id') ?: $response->json('id');

        $model = new $this->model();
        $keyName = $model->getKeyName();
        $testData += [
            $keyName => $id,
        ];

        $this->assertDatabaseHas($model->getTable(), $testData);

        $testResponse = $testJsonData ?: $testData;
        $response->assertJsonFragment($testResponse);

        return $response;
    }

    protected abstract function routeStore();

    protected abstract function routePut();

    protected abstract function model();
}
