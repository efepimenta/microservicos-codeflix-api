<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\BasicCrudController;
use App\Http\Resources\VideoResource;
use Tests\Stubs\Models\VideoStub;

class VideoControllerStub extends BasicCrudController
{

    protected function model()
    {
        return VideoStub::class;
    }

    protected function rulesStore()
    {
        return [
            'title' => 'required',
            'description' => 'required',
            'year_launched' => 'required',
            'rating' => 'required',
            'duration' => 'required',
            'opened' => 'boolean',
            'categories_id' => 'boolean',
            'genres_id' => 'boolean',
        ];
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function resource()
    {
        return VideoResource::class;
    }
}
