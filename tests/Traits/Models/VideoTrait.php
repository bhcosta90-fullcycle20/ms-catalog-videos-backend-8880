<?php

declare(strict_types=1);

namespace Tests\Traits\Models;

use Illuminate\Http\UploadedFile;

trait VideoTrait {
    protected function getFiles()
    {
        return [
            'video_file' => UploadedFile::fake()->create('video.mp4'),
            'trailler_file' => UploadedFile::fake()->create('video.mp4'),
            'thumb_file' => UploadedFile::fake()->create('video.jpg'),
            'banner_file' => UploadedFile::fake()->create('video.jpg'),
        ];
    }
}