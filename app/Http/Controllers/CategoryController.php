<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;

class CategoryController extends Abstracts\BasicCrudController
{
    private $rules = [
        'name' => 'required|min:3|max:100',
        'description' => 'nullable|max:1000',
        'is_active' => 'nullable|boolean',
    ];

    protected function model()
    {
        return new Category;
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
