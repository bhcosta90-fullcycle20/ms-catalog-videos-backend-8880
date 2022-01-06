<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dir = Storage::getDriver()->getAdapter()->getPathPrefix();
        File::deleteDirectory($dir, true);

        $genres = Genre::all();

        Video::factory(100)->make()->each(function ($makeVideo) use ($genres) {
            $objVideo = Video::create($makeVideo->toArray() + [
                'thumb_file' => $this->getImageFile(),
                'banner_file' => $this->getImageFile(),
                'trailer_file' => $this->getVideoFile(),
                'video_file' => $this->getVideoFile(),
            ]);
            $subGenres = $genres->random(5)->load('categories');
            $categoriesId = [];
            foreach ($subGenres as $genre) {
                array_push($categoriesId, ...$genre->categories->pluck('id')->toArray());
            }
            $categoriesId = array_unique($categoriesId);
            $genresId = $subGenres->pluck('id')->toArray();
            $objVideo->categories()->attach($categoriesId);
            $objVideo->genres()->attach($genresId);
        });
    }

    private function getVideoFile()
    {
        return new UploadedFile(storage_path('faker/video.mp4'), 'video.mp4');
    }

    private function getImageFile()
    {
        return new UploadedFile(storage_path('faker/laravel.png'), 'laravel.png');
    }
}
