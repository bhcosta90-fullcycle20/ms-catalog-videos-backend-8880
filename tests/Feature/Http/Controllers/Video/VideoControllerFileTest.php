<?php

namespace Tests\Feature\Http\Controllers\Video;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Traits\TestSave;
use Tests\Traits\TestUploads;

class VideoControllerFileTest extends BaseVideoControllerAbstract
{
    use TestSave, TestUploads;

    public function testStoreWithFiles()
    {
        Storage::fake();
        $files = $this->getFiles();

        $category = Category::factory()->create();
        $genre = Genre::factory()->create();
        $genre->categories()->attach($category);

        $dataSend = $this->sendData + [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id],
        ] + $files;

        $response = $this->postJson($this->routeStore(), $dataSend)
            ->assertStatus(201);

        $id = $response->json('id') ?: $response->json('data.id');

        foreach ($files as $file) {
            Storage::exists("{$id}/{$file->hashName()}");
        }
    }

    public function testUpdateWithFiles()
    {
        Storage::fake();
        $files = $this->getFiles();

        $category = Category::factory()->create();
        $genre = Genre::factory()->create();
        $genre->categories()->attach($category);

        $dataSend = $this->sendData + [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id],
        ] + $files;

        $response = $this->putJson($this->routePut(), $dataSend)
            ->assertStatus(200);

        $id = $response->json('id') ?: $response->json('data.id');

        foreach ($files as $file) {
            Storage::exists("{$id}/{$file->hashName()}");
        }
    }

    private function getFiles()
    {
        return [
            'video_file' => UploadedFile::fake()->create('video.mp4'),
        ];
    }
}
