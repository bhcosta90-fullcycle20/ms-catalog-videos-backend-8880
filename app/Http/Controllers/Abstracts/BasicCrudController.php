<?php

namespace App\Http\Controllers\Abstracts;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;

abstract class BasicCrudController extends Controller
{
    protected abstract function model();

    protected abstract function ruleStore();

    protected abstract function rulePut();

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

    public function show($id)
    {
        return $this->findOrFail($id);
    }

    public function update(Request $request, $id){
        $data = $this->validate($request, $this->rulePut());
        $obj = $this->findOrFail($id);
        $obj->update($data);
        return $obj;
    }

    public function destroy($id){
        $this->findOrFail($id)->delete();
        return response()->noContent();
    }

    protected function findOrFail(int|string $value)
    {
        $model = $this->model();
        $keyName = $model->getRouteKeyName();
        return $this->model()->where($keyName, $value)->firstOrFail();
    }
}
