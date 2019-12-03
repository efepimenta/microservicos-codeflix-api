<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends BasicCrudController
{

//    public function index()
//    {
//        // com resource
////        return CategoryResource::collection(parent::index());
//
//        // com collection
////        return new CategoryCollection(parent::index());
//
//        // saida normal
//        return parent::index();
//    }

    protected function model()
    {
        return Category::class;
    }

    protected function rulesStore()
    {
        return [
            'name' => 'required|max:255',
            'description' => 'string',
            'is_active' => 'boolean'
        ];
    }

    protected function rulesUpdate()
    {
        return [
            'name' => 'required|max:255',
            'description' => 'string',
            'is_active' => 'boolean'
        ];
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function resource()
    {
        return CategoryResource::class;
    }

}
