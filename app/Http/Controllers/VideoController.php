<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Rules\GenresHasCategoriesRule;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VideoController extends Abstracts\BasicCrudController
{
    private array $rules = [];

    public function __construct()
    {
        $this->rules = [
            'title' => 'required|min:3|max:100',
            'description' => 'required',
            'year_launched' => 'required|date_format:Y',
            'opened' => 'nullable|boolean',
            'rating' => "required|in:" . implode(',', Video::RATINGS),
            'duration' => 'required|integer',
            'categories_id' => "required|array|exists:categories,id,deleted_at,NULL",
            'genres_id' => ["required", "array", "exists:genres,id,deleted_at,NULL"],
            'video_file' => 'nullable|mimetypes:video/mp4|max:' . Video::VIDEO_FILE_MAX_SIZE,
            'trailler_file' => 'nullable|mimetypes:video/mp4|max:' . Video::TRAILLER_FILE_MAX_SIZE,
            'banner_file' => 'nullable|mimes:jpg,jpeg,png|max:' . Video::BANNER_FILE_MAX_SIZE,
            'thumb_file' => 'nullable|mimes:jpg,jpeg,png|max:' . Video::THUMB_FILE_MAX_SIZE,
        ];
    }

    protected function model(): Model
    {
        return new Video;
    }

    protected function ruleStore()
    {
        return $this->rules;
    }

    protected function rulePut()
    {
        return $this->ruleStore();
    }

    protected function handleRelations(Video $video, $data): Video
    {
        $video->categories()->sync(array_unique($data['categories_id']));
        $video->genres()->sync(array_unique($data['genres_id']));
        return $video;
    }

    protected function addRuleGenreHasCategory(Request $request)
    {
        $categories = is_array($request->get('categories_id')) ? $request->get('categories_id') : [];
        $this->rules['genres_id'][] = new GenresHasCategoriesRule($categories);
    }
}
