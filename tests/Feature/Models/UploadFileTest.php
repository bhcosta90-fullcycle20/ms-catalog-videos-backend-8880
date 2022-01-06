<?php

namespace Tests\Feature\Models;

use Illuminate\Support\Facades\Storage;
use Tests\Stubs\Models\UploadFileStub;
use Tests\TestCase;

class UploadFileTest extends TestCase
{
    private UploadFileStub $obj;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new UploadFileStub;
        Storage::fake();

        UploadFileStub::dropTable();
        UploadFileStub::createTable();
    }

    protected function tearDown(): void
    {
        UploadFileStub::dropTable();
        parent::tearDown();
    }

    public function testMakeOldFieldOnSaving()
    {
        $this->obj->fill([
            'name' => 'oi',
            'file1' => 'teste.jpg',
            'file2' => 'teste2.jpg',
        ]);
        $this->obj->save();

        $this->assertCount(0, $this->obj->oldFiles);

        $this->obj->update([
            'name' => 'oi2',
            'file1' => 'teste3.jpg'
        ]);

        $this->assertEqualsCanonicalizing(['teste.jpg'], $this->obj->oldFiles);
    }

    public function testMakeOldFilesNullOnSaving()
    {
        $this->obj->fill([
            'name' => 'oi2',
            'file1' => null,
            'file2' => null,
        ]);
        $this->obj->save();

        $this->obj->update([
            'name' => 'oi2',
            'file1' => 'teste3.jpg'
        ]);

        $this->assertEqualsCanonicalizing([], $this->obj->oldFiles);
    }
}
