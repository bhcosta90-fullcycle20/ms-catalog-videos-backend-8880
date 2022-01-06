<?php

declare(strict_types=1);

namespace Tests\Traits;

use Exception;
use Illuminate\Testing\TestResponse;

trait TestSave
{

    protected abstract function routeStore();

    protected abstract function routePut();

    protected abstract function model();

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

        $this->assertInDatabase($response, $testData);
        $this->assertJsonResponseContent($response, $testData, $testJsonData);

        return $response;
    }

    protected function assertUpdate(
        array $sendData,
        array $testData,
        array $testJsonData = null
    ): TestResponse {
        $model = new $this->model();

        /** @var TestResponse $response */
        $response = $this->putJson($this->routePut(), $sendData);

        if (!in_array($response->status(), [200, 201])) {
            throw new Exception("Response status must be 200 or 201, given: {$response->status()}: {$response->content()}");
        }

        $id = $response->json('data.id') ?: $response->json('id');

        $keyName = $model->getKeyName();
        $testData += [
            $keyName => $id,
        ];

        try {
            $this->assertInDatabase($response, $testData);
            $testResponse = $testJsonData ?: $testData;
            $response->assertJsonFragment($testResponse);
        } catch (Exception $e) {
            dump($sendData, $response->json());
            throw $e;
        }

        return $response;
    }

    private function assertInDatabase(TestResponse $response, array $data)
    {
        $model = new $this->model();
        $keyName = $model->getKeyName();
        $table = $model->getTable();

        $this->assertDatabaseHas($table, $data + [
            $keyName => $this->getIdFromResponse($response)
        ]);
    }

    private function assertJsonResponseContent(
        TestResponse $response,
        array $testDatabase,
        array $testJsonData = null
    ) {
        $testResponse = $testJsonData ?: $testDatabase;
        $response->assertJsonFragment($testResponse, $testResponse + ['id' => $this->getIdFromResponse($response)]);
    }

    private function getIdFromResponse(TestResponse $testResponse): int|string|null
    {
        return $testResponse->json('id') ?: $testResponse->json('data.id');
    }
}
