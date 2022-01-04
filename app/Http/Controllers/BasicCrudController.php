<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;

abstract class BasicCrudController extends Controller
{
    protected abstract function model();

    protected abstract function ruleStore();

    public function index()
    {
        return $this->model()::all();
    }

    public function store(Request $request) {
        $data = $this->validate($request, $this->ruleStore());
        $obj = $this->model()::create($data);
        $obj->refresh();
        return $obj;
    }

    public function show()
    {

    }

    protected function findOrFail(int|string $value)
    {
        $model = $this->model();
        $keyName = $model->getRouteKeyName();
        return $this->model()->where($keyName, $value)->firstOrFail();
    }

    // public function show(Category $category)
    // {
    //     return $category;
    //     return new CategoryResource($category);
    // }

    // public function update(Request $request, Category $category)
    // {
    //     $data = $this->validate($request, $this->rules);
    //     $category->update($data);

    //     return $category;
    //     return new CategoryResource($category);
    // }

    // public function destroy(Category $category)
    // {
    //     $category->delete();

    //     return response()->noContent();
    // }
}
