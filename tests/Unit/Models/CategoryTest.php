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
        $this->assertEquals('uuid', $obj->getKeyName());
    }

    public function testIfUseTraits()
    {
        $traits = [
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            \App\Models\Traits\Uuid::class,
        ];

        $modelTraits = array_keys(class_uses(Model::class));

        foreach ($traits as $trait) {
            $this->assertContains($trait, $modelTraits);
        }
    }

    private function getModel()
    {
        return new Model;
    }
}
