<?php

namespace Tests\Feature\Models;

use App\Models\Video as Model;
use Exception;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\Exceptions\TestException;
use Tests\Feature\Models\Video\BaseVideo;
use Tests\Traits\Models\VideoTrait;

class VideoUploadTest extends BaseVideo
{
    use RefreshDatabase, VideoTrait;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
    }

    public function testCreateWithFiles()
    {
        $video = Model::create($this->sendData + $this->getFiles());
        foreach (array_keys($this->getFiles()) as $key) {
            Storage::assertExists($video->relativePath($video->{$key}));
        }
    }

    public function testRollbackCreateWithFile()
    {
        Event::listen(TransactionCommitted::class, fn () => throw new TestException('error file'));

        try {
            Model::create($this->sendData + $this->getFiles());
        } catch (Exception $e) {
            $this->assertEquals('error file', $e->getMessage());
            $this->assertCount(0, Storage::allFiles());
        }
    }

    public function testUpdateWithFiles()
    {
        $video = Model::create($this->sendData);
        $video->update($this->getFiles());

        $files = [];
        foreach ($keysValues = array_keys($this->getFiles()) as $key) {
            $files[] = $video->id . '/' . $video->{$key};
            Storage::assertExists($video->relativePath($video->{$key}));
        }

        $newVideo = UploadedFile::fake()->create('video.mp4');
        $video->update([
            $keysValues[0] => $newVideo,
        ]);

        Storage::assertMissing($files[0]);
        Storage::assertExists($files[1]);
        Storage::assertExists($video->relativePath($newVideo->hashName()));
    }

    public function testRollbackUpdateWithFiles()
    {
        $video = Model::create($this->sendData);

        Event::listen(TransactionCommitted::class, fn () => throw new TestException('error file'));

        try {
            $video->update($this->getFiles());
        } catch (Exception $e) {
            $this->assertEquals('error file', $e->getMessage());
            $this->assertCount(0, Storage::allFiles());
        }
    }
}
