<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BasicCrudController extends Controller
{

    protected abstract function model();
    protected abstract function rulesStore();

    public function index()
    {
        return $this->model()::all();
    }

    public function store(Request $request)
    {
        $validData = $this->validate($request, $this->rulesStore());
        $obj = $this->model()::create($validData);
        $obj->refresh();
        return $obj;
    }

    protected function findOrFail($id) {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->model()::where($keyName, $id)->firstOrFail();
    }

    public function show($id)
    {
        return $this->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $validData = $this->validate($request, $this->rulesStore());
        $toUpdateModel = $this->findOrFail($id);
        $toUpdateModel->update($validData);
        return $toUpdateModel;
    }

    public function destroy($id)
    {
        $toUpdateModel = $this->findOrFail($id);
        $toUpdateModel->delete();
        return response()->noContent();
    }
}
