<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends BasicCrudController
{
    protected function model()
    {
        return Genre::class;
    }

    protected function rulesStore()
    {
        return [
            'name' => 'required|max:100',
            'is_active' => 'boolean',
            'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
        ];
    }

    public function store(Request $request)
    {
        $validData = $this->validate($request, $this->rulesStore());
        $self = $this;
        /** @var Genre $genre */
        $genre = \DB::transaction(function () use ($request, $validData, $self) {
            $toCreateModel = $this->model()::create($validData);
            $self->handleRelations($toCreateModel, $request);
            return $toCreateModel;
        });
        $genre->refresh();
        return $genre;
    }

    public function update(Request $request, $id)
    {
        $toUpdateModel = $this->findOrFail($id);
        $validData = $this->validate($request, $this->rulesStore());
        $self = $this;
        /** @var Genre $genre */
        $genre = \DB::transaction(function () use ($request, $validData, $toUpdateModel, $self) {
            $toUpdateModel->update($validData);
            $self->handleRelations($toUpdateModel, $request);
            return $toUpdateModel;
        });
        return $genre;
    }

    protected function handleRelations($genre, Request $request)
    {
        $genre->categories()->sync($request->get('categories_id'));
    }
}
