<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;

class CategoryController extends BasicCrudController
{
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
}
