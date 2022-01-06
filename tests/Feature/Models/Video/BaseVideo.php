<?php

namespace Tests\Feature\Models\Video;

use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class BaseVideo extends TestCase
{
    use RefreshDatabase;

    protected $sendData = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->sendData = [
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 1990,
            'rating' => Video::RATINGS[0],
            'duration' => 60,
        ];
    }
}