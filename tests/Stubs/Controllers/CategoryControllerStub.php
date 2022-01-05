<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Abstracts\BasicCrudController;
use Illuminate\Database\Eloquent\Model;
use Tests\Stubs\Models\CategoryStub;

class CategoryControllerStub extends BasicCrudController
{
    protected function model(): Model
    {
        return new CategoryStub;
    }

    protected function ruleStore() {
        return [
            'name' => 'required|min:3|max:100',
            'description' => 'nullable|max:1000',
        ];
    }

    protected function rulePut()
    {
        return $this->ruleStore();
    }
}
