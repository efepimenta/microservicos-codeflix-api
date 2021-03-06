<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Rules\GenresHasCategoriesRules;
use Illuminate\Http\Request;

class VideoController extends BasicCrudController
{

    protected $rules = [];

    public function __construct()
    {
        $this->rules = [
            'title' => 'required|max:255',
            'description' => 'required',
            'year_launched' => 'required|date_format:Y',
            'rating' => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration' => 'required|integer',
            'opened' => 'boolean',
            'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
            'genres_id' => [
                'required',
                'array',
                'exists:genres,id,deleted_at,NULL',
            ],
            'video_file' => 'mimetypes:video/mp4|max:'.Video::VIDEO_FILE_MAX_SIZE,
            'thumb_file' => 'mimetypes:image/png,image/jpeg|max:'.Video::THUMB_FILE_MAX_SIZE,
            'banner_file' => 'mimetypes:image/png,image/jpeg|max:'.Video::BANNER_FILE_MAX_SIZE,
            'trailer_file' => 'mimetypes:video/mp4|max:'.Video::TRAILER_FILE_MAX_SIZE,
        ];
    }

    public function store(Request $request)
    {
        $this->addRuleGenreHasCategory($request);
        $validData = $this->validate($request, $this->rulesStore());
        $video = $this->model()::create($validData);
        $video->refresh();
        $resource = $this->resource();
        return new $resource($video);
    }

    public function update(Request $request, $id)
    {
        $this->addRuleGenreHasCategory($request);
        $video = $this->findOrFail($id);
        $validData = $this->validate($request, $this->rulesStore());
        $video->update($validData);
        $resource = $this->resource();
        return new $resource($video);
    }

    protected function addRuleGenreHasCategory(Request $request)
    {
        $categoriesId = $request->get('categories_id');
        $categoriesId = is_array($categoriesId) ? $categoriesId : [];
        $this->rules['genres_id'][] = new GenresHasCategoriesRules($categoriesId);
    }

    protected function model()
    {
        return Video::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
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
