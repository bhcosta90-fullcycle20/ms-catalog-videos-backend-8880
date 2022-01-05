<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Model;

class GenreController extends Abstracts\BasicCrudController
{
    private $rules = [
        'name' => 'required|min:3|max:100',
        'is_active' => 'nullable|boolean',
        'categories_id' => "required|array|exists:categories,id",
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
}
