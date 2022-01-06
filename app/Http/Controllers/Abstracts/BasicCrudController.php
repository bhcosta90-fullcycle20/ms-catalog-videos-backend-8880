<?php

namespace App\Http\Controllers\Abstracts;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use ReflectionClass;

abstract class BasicCrudController extends Controller
{
    protected $paginateSize = 15;

    protected abstract function model(): Model;

    protected abstract function resource(): string;

    protected function resourceCollection(): string|null
    {
        return null;
    }

    protected abstract function ruleStore();

    protected abstract function rulePut();

    public function index()
    {
        $data = !$this->paginateSize ? $this->model()::all() : $this->model()::paginate($this->paginateSize);

        $resourceCollection = $this->resourceCollection();

        if (is_null($resourceCollection)) {
            $resource = $this->resource();
            return $resource::collection($data);
        }

        $refClass = new ReflectionClass($resourceCollection);
        $isCollectionClass = $refClass->isSubclassOf(ResourceCollection::class);

        return $isCollectionClass ? new $resourceCollection($data) : $resourceCollection::collection($data);
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, $this->ruleStore());
        $obj = $this->model()::create($data);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function show($id)
    {
        $obj = $this->findOrFail($id);
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function update(Request $request, $id)
    {
        $data = $this->validate($request, $this->rulePut());
        $obj = $this->findOrFail($id);
        $obj->update($data);
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function destroy($id)
    {
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
