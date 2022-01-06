<?php

namespace Tests\Unit\Models;

use App\Models\Video as Model;
use PHPUnit\Framework\TestCase;

class VideoUnitTest extends TestCase
{
    public function testFillable()
    {
        $obj = $this->getModel();

        $this->assertEquals([
            'title',
            'description',
            'year_launched',
            'opened',
            'rating',
            'duration',
        ], $obj->getFillable());
    }

    public function testKeyType()
    {
        $obj = $this->getModel();
        $this->assertEquals('string', $obj->getKeyType());
    }

    public function testIncrementing()
    {
        $obj = $this->getModel();
        $this->assertEquals(false, $obj->getIncrementing());
    }

    public function testKeyName()
    {
        $obj = $this->getModel();
        $this->assertEquals('id', $obj->getKeyName());
    }

    public function testIfUseTraits()
    {
        $traits = [
            \Illuminate\Database\Eloquent\Factories\HasFactory::class,
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            \App\Models\Traits\Uuid::class,
            \App\Models\Traits\UploadFile::class,
        ];

        $modelTraits = array_keys(class_uses(Model::class));
        $this->assertEqualsCanonicalizing($traits, $modelTraits);
    }

    public function testCasts()
    {
        $obj = $this->getModel();
        $this->assertEqualsCanonicalizing([
            "id" => "string",
            "year_launched" => "integer",
            "opened" => "boolean",
            "duration" => "integer",
            "deleted_at" => "datetime",
        ], $obj->getCasts());
    }

    private function getModel()
    {
        return new Model;
    }
}
