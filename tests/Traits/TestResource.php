<?php

declare(strict_types=1);

namespace Tests\Traits;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Testing\TestResponse;

trait TestResource
{
    public function assertResource(TestResponse $response, JsonResource $jsonResource)
    {
        $response->assertJson($jsonResource->response()->getData(true));
    }
}