<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BasicCrudController extends Controller
{

    protected $paginationSize = 15;

    protected abstract function model();
    protected abstract function rulesStore();
//    protected abstract function rulesUpdate();
    protected abstract function resource();
    protected abstract function resourceCollection();

    public function index()
    {
        $data = !$this->paginationSize ? $this->model()->all() : $this->model()::paginate($this->paginationSize);
        $resourceCollectionClass = $this->resourceCollection();
        $refClass = new \ReflectionClass($resourceCollectionClass);
        return $refClass->isSubclassOf(ResourceCollection::class)
            ? new $resourceCollectionClass($data)
            : $resourceCollectionClass::collection($data);
    }

    public function store(Request $request)
    {
        $validData = $this->validate($request, $this->rulesStore());
        $obj = $this->model()::create($validData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    protected function findOrFail($id) {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->model()::where($keyName, $id)->firstOrFail();
    }

    public function show($id)
    {
        $resource = $this->resource();
        return new $resource($this->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $validData = $this->validate($request, $this->rulesStore());
        $toUpdateModel = $this->findOrFail($id);
        $toUpdateModel->update($validData);
        $resource = $this->resource();
        return new $resource($toUpdateModel);
    }

    public function destroy($id)
    {
        $toUpdateModel = $this->findOrFail($id);
        $toUpdateModel->delete();
        return response()->noContent();
    }
}
