<?php

namespace App\Http\Controllers;

use App\Models\Genre;

class GenreController extends Abstracts\BasicCrudController
{
    private $rules = [
        'name' => 'required|min:3|max:100',
        'is_active' => 'nullable|boolean',
    ];

    protected function model()
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
