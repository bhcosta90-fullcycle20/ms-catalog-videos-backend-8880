<?php

namespace App\Http\Controllers;

use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenreController extends Abstracts\BasicCrudController
{
    private $rules = [
        'name' => 'required|min:3|max:100',
        'is_active' => 'nullable|boolean',
        'categories_id' => "required|array|exists:categories,id,deleted_at,NULL",
    ];

    protected function model(): Model
    {
        return new Genre;
    }

    protected function ruleStore()
    {
        return $this->rules;
    }

    protected function rulePut()
    {
        return $this->rules;
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, $this->ruleStore());
        $self = $this;

        $obj = DB::transaction(function () use ($data, $self) {
            $obj = $this->model()::create($data);
            $self->handleRelations($obj, $data);
            return $obj;
        });

        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function update(Request $request, $id)
    {
        $data = $this->validate($request, $this->rulePut());
        $obj = $this->findOrFail($id);

        $self = $this;
        $obj = DB::transaction(function () use ($obj, $data, $self) {
            /** @var Genre $obj */
            $obj->update($data);
            $self->handleRelations($obj, $data);
            return $obj;
        });

        $resource = $this->resource();
        return new $resource($obj);
    }

    protected function handleRelations(Genre $genre, $data): Genre
    {
        $genre->categories()->sync(array_unique($data['categories_id']));
        return $genre;
    }

    protected function resource(): string
    {
        return GenreResource::class;
    }
}
