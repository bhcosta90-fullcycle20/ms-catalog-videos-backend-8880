<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private $rules = [
        'name' => 'required|min:3|max:100',
        'description' => 'nullable|max:1000',
        'is_active' => 'nullable|boolean',
    ];

    public function index()
    {
        return Category::all();
        // return CategoryResource::collection(Category::paginate());
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, $this->rules);

        $category = Category::create($data);
        $category->refresh();

        return new CategoryResource($category);
    }

    public function show(Category $category)
    {
        return $category;
        return new CategoryResource($category);
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validate($request, $this->rules);
        $category->update($data);

        return new CategoryResource($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return response()->noContent();
    }
}
