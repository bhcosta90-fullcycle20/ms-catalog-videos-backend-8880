<?php

namespace Tests\Feature\Http\Controllers\Video;

use App\Models\Category;
use App\Models\Genre;
use Tests\Traits\TestUploads;

class VideoControllerErrorTest extends BaseVideoControllerAbstract
{
    use TestUploads;
    
    public function testInvalidateRequired()
    {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
            'categories_id' => '',
            'genres_id' => '',
        ];

        $this->assertInvalidationStore($data, "required");
        $this->assertInvalidationUpdate($data, "required");
    }

    public function testInvalidateMinString()
    {
        $data = [
            'title' => 'a',
        ];

        $this->assertInvalidationStore($data, "min.string", ['min' => 3]);
        $this->assertInvalidationUpdate($data, "min.string", ['min' => 3]);
    }

    public function testInvalidateMaxString()
    {
        $data = [
            'title' => str_repeat('a', 500),
        ];

        $this->assertInvalidationStore($data, "max.string", ['max' => 100]);
        $this->assertInvalidationUpdate($data, "max.string", ['max' => 100]);
    }

    public function testInvalidateInteger()
    {
        $data = [
            'duration' => 'a',
        ];

        $this->assertInvalidationStore($data, "integer");
        $this->assertInvalidationUpdate($data, "integer");
    }

    public function testInvalidateBoolean()
    {
        $data = [
            'opened' => 'a',
        ];

        $this->assertInvalidationStore($data, "boolean");
        $this->assertInvalidationUpdate($data, "boolean");
    }

    public function testInvalidateDateFormatYear()
    {
        $data = [
            'year_launched' => 'a',
        ];

        $this->assertInvalidationStore($data, "date_format", ['format' => 'Y']);
        $this->assertInvalidationUpdate($data, "date_format", ['format' => 'Y']);
    }

    public function testInvalidateIn()
    {
        $data = [
            'rating' => 'a',
        ];

        $this->assertInvalidationStore($data, "in");
        $this->assertInvalidationUpdate($data, "in");
    }

    public function testInvalidateArray()
    {
        $data = [
            'categories_id' => '123',
            'genres_id' => '123',
        ];

        $this->assertInvalidationStore($data, "array");
        $this->assertInvalidationUpdate($data, "array");
    }

    public function testInvalidateExists()
    {
        $data = [
            'categories_id' => ['123'],
            'genres_id' => ['123'],
        ];

        $this->assertInvalidationStore($data, "exists");
        $this->assertInvalidationUpdate($data, "exists");

        $category = Category::factory()->create();
        $category->delete();
        $genre = Genre::factory()->create();
        $genre->delete();

        $data = [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id],
        ];

        $this->assertInvalidationStore($data, "exists");
        $this->assertInvalidationUpdate($data, "exists");
    }

    public function testInvalidationVideoField()
    {
        $this->assertInvalidationFile('video_file', 'mp4', 12, 'mimetypes', ['values' => 'video/mp4']);
    }
}