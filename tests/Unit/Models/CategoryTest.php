<?php

namespace Tests\Unit\Models;

use App\Models\Category as Model;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    public function testFillable()
    {
        $obj = $this->getModel();

        $this->assertEquals([
            'name',
            'description',
            'is_active',
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
        ];

        $modelTraits = array_keys(class_uses(Model::class));
        $this->assertEqualsCanonicalizing($traits, $modelTraits);
    }

    public function testCasts()
    {
        $obj = $this->getModel();
        $this->assertEqualsCanonicalizing([
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
            'uuid' => 'string',
        ], $obj->getCasts());
    }

    private function getModel()
    {
        return new Model;
    }
}
