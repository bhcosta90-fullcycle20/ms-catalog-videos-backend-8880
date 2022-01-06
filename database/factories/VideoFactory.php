<?php

namespace Database\Factories;

use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(20),
            'year_launched' => rand(1985, date('Y')),
            'opened' => rand(0, 1),
            'duration' => rand(1, 30),
            'rating' => $this->faker->randomElement(Video::RATINGS),
            // 'thumb_file' => null,
            // 'banner_file' => null,
            // 'trailer_file' => null,
            // 'video_file' => null,
            // 'published' => rand(0, 1),
        ];
    }
}
