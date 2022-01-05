<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $genres = Genre::all();

        Video::factory(100)->create()->each(function ($objVideo) use ($genres) {
            $categories = [];
            $genres = $genres->random(3);
            foreach ($genres as $genre) {
                $categories += $genre->categories()->pluck('categories.id')->toArray();
            }
            $objVideo->categories()->attach($categories);
            $objVideo->genres()->attach($genres);
        });
    }
}
