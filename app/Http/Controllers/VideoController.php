<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class VideoController extends Abstracts\BasicCrudController
{
    protected function model(): Model
    {
        return new Video;
    }

    protected function ruleStore()
    {
        return [
            'title' => 'required|min:3|max:100',
            'description' => 'required',
            'year_launched' => 'required|date_format:Y',
            'opened' => 'nullable|boolean',
            'rating' => "required|in:" . implode(',', Video::RATINGS),
            'duration' => 'required|integer'
        ];
    }

    protected function rulePut()
    {
        return $this->ruleStore();
    }
}
