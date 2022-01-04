<?php

declare(strict_types=1);

namespace Tests\Traits;

use Illuminate\Support\Facades\Lang;
use Illuminate\Testing\TestResponse;

trait TestValidation
{
    protected function assertInvalidationStore(
        array $data,
        string $rule,
        array $ruleParams = []
    ) {
        $response = $this->postJson($this->routeStore(), $data);
        $fields = array_keys($data);
        $this->assertInvalidationFields($response, $fields, $rule, $ruleParams);
    }

    protected function assertInvalidationUpdate(
        array $data,
        string $rule,
        array $ruleParams = []
    ) {
        $response = $this->putJson($this->routePut(), $data);
        $fields = array_keys($data);
        $this->assertInvalidationFields($response, $fields, $rule, $ruleParams);
    }

    protected function assertInvalidationFields(
        TestResponse $response,
        array $fields,
        string $rule,
        array $ruleParams = []
    ): TestResponse {
        $response->assertStatus(422)
            ->assertJsonValidationErrors($fields);

        foreach ($fields as $field) {
            $fieldName = str_replace('_', ' ', $field);
            $response->assertJsonFragment([Lang::get("validation." . $rule, ['attribute' => $fieldName] + $ruleParams)]);
        }

        return $response;
    }

    protected abstract function routeStore();
    
    protected abstract function routePut();
}
