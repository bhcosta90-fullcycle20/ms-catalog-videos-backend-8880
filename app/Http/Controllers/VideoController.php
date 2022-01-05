<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'duration' => 'required|integer',
            'categories_id' => "required|array|exists:categories,id",
            'genres_id' => "required|array|exists:genres,id",
        ];
    }

    protected function rulePut()
    {
        return $this->ruleStore();
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, $this->ruleStore());

        $obj = DB::transaction(function () use ($data) {
            $obj = $this->model()::create($data);
            $obj->categories()->attach(array_unique($data['categories_id']));
            $obj->genres()->attach(array_unique($data['genres_id']));
            return $obj;
        });

        $obj->refresh();
        return $obj;
    }

    public function update(Request $request, $id)
    {
        $data = $this->validate($request, $this->rulePut());

        $obj = DB::transaction(function () use ($id, $data) {
            /** @var Video $obj */
            $obj = $this->findOrFail($id);
            $obj->update($data);

            $obj->categories()->sync(array_unique($data['categories_id']));
            $obj->genres()->sync(array_unique($data['genres_id']));
            return $obj;
        });

        return $obj;
    }
}
