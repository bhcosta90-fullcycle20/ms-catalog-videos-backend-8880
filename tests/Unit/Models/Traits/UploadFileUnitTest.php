<?php

namespace Tests\Unit\Models\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Stubs\Models\UploadFileStub;

class UploadFileUnitTest extends TestCase
{
    private UploadFileStub $obj;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new UploadFileStub;
        Storage::fake();
    }

    public function testUploadFile()
    {
        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);
        Storage::assertExists($this->obj->relativePath($file->hashName()));
    }

    public function testUploadFiles()
    {
        $file = UploadedFile::fake()->create('video.mp4');
        $file2 = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFiles([$file, $file2]);
        Storage::assertExists($this->obj->relativePath($file->hashName()));
        Storage::assertExists($this->obj->relativePath($file2->hashName()));
    }

    public function testDeleteFile()
    {
        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);
        $this->obj->deleteFile($file->hashName());
        Storage::assertMissing($this->obj->relativePath($file->hashName()));

        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);
        $this->obj->deleteFile($file);
        Storage::assertMissing($this->obj->relativePath($file->hashName()));
    }

    public function testDeleteOldFiles()
    {
        $file = UploadedFile::fake()->create('video.mp4');
        $file2 = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFiles([$file, $file2]);
        $this->obj->deleteOldFiles();
        $this->assertCount(2, Storage::allFiles());

        $this->obj->oldFiles = [$file->hashName()];
        $this->obj->deleteOldFiles();
        Storage::assertMissing($this->obj->relativePath($file->hashName()));
        Storage::assertExists($this->obj->relativePath($file2->hashName()));
    }


    public function testDeleteFiles()
    {
        $file = UploadedFile::fake()->create('video.mp4');
        $file2 = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFiles([$file, $file2]);
        $this->obj->deleteFiles([$file, $file2->hashName()]);

        Storage::assertMissing($this->obj->relativePath($file->hashName()));
        Storage::assertMissing($this->obj->relativePath($file2->hashName()));
    }

    public function testExtractFiles()
    {
        $attributes = [];
        $files = UploadFileStub::extractFiles($attributes);
        $this->assertCount(0, $files);

        $attributes = ['file1' => 'teste'];
        $files = UploadFileStub::extractFiles($attributes);
        $this->assertCount(1, $attributes);
        $this->assertEquals(['file1' => 'teste'], $attributes);
        $this->assertCount(0, $files);

        $attributes = ['file1' => 'teste', 'file2' => 'teste'];
        $files = UploadFileStub::extractFiles($attributes);
        $this->assertCount(2, $attributes);
        $this->assertEquals(['file1' => 'teste', 'file2' => 'teste'], $attributes);
        $this->assertCount(0, $files);

        $file = UploadedFile::fake()->create('video.mp4');
        $attributes = ['file1' => $file, 'file2' => 'teste'];
        $files = UploadFileStub::extractFiles($attributes);
        $this->assertCount(2, $attributes);
        $this->assertEquals(['file1' => $file->hashName(), 'file2' => 'teste'], $attributes);
        $this->assertEquals([$file], $files);

        $file2 = UploadedFile::fake()->create('video.mp4');
        $attributes = ['file1' => $file, 'file2' => $file2, 'other' => 'test'];
        $files = UploadFileStub::extractFiles($attributes);
        $this->assertCount(3, $attributes);
        $this->assertEquals(['file1' => $file->hashName(), 'file2' => $file2->hashName(), 'other' => 'test'], $attributes);
        $this->assertEquals([$file, $file2], $files);
    }

    public function testRelativePath()
    {
        $file = UploadedFile::fake()->create('video.mp4');
        $this->assertEquals("1/" . $file->hashName(), $this->obj->relativePath($file->hashName()));
    }
}
