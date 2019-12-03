<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\VideoResource;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\QueryException;
use Tests\Feature\Http\Controllers\Api\Video\BaseVideoApiTest;
use Tests\Traits\TestApiResources;
use Tests\Types\InvalidationTypes;

class VideoApiCrudTest extends BaseVideoApiTest
{

    use TestApiResources;

    public function testIndex()
    {
        $video = factory(Video::class)->create();
        $response = $this->get(route('videos.index'));
        $this->assertOk($response, ['data' => [$video->toArray()]]);

        $response->assertJsonStructure([
            'data' => [],
            'links' => [],
            'meta' => [],
        ])->assertJson([
            'meta' => ['per_page' => 15]
        ]);
        $resource = VideoResource::collection(collect([$video]));

        $json = $resource->response();
        $response->assertJson($json->getData(true));
    }

    public function testShow()
    {
        $video = factory(Video::class)->create();
        $response = $this->get(route('videos.show', ['video' => $video->id]));
        $this->assertOk($response, ['data' => $video->toArray()]);

        $video = Video::find($response->json('data.id'));
        $this->assertApiResource($response, VideoResource::class, $video);
    }

    public function testInvalidationData()
    {
        $this->assertInvalidationInStoreAction(route('videos.store'), [], [
            new InvalidationTypes('title', 'required'),
            new InvalidationTypes('categories_id', 'required'),
            new InvalidationTypes('genres_id', 'required'),
        ]);

        $this->assertInvalidationInStoreAction(route('videos.store'), [
            'title' => str_repeat('a', 256),
            'opened' => 'a',
            'categories_id' => ['asdf'],
            'genres_id' => ['asdf'],
        ], [
            new InvalidationTypes('title', 'max.string', ['max' => 255]),
            new InvalidationTypes('opened', 'boolean'),
            new InvalidationTypes('categories_id', 'exists'),
            new InvalidationTypes('genres_id', 'exists'),
        ]);

        $video = factory(Video::class)->create();
        $this->assertInvalidationInUpdateAction(route('videos.update', ['video' => $video->id]), [
            'title' => '',
            'opened' => 'a'
        ], [
            new InvalidationTypes('title', 'required'),
            new InvalidationTypes('opened', 'boolean'),
        ]);
    }

    public function testStore()
    {
        $category = factory(Category::class)->create([
            'name' => 'test 1'
        ]);
        $genre = factory(Genre::class)->create([
            'name' => 'test 1'
        ]);
        $genre->categories()->sync([$category->id]);

        $response = $this->json('POST', route('videos.store'), [
            'title' => 'test',
            'description' => 'test',
            'year_launched' => 2015,
            'rating' => Video::RATING_LIST[0],
            'duration' => 50,
            'opened' => true,
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id],
        ]);
        $video = Video::find($response->json('data.id'));
        $this->assertOk($response, ['data' => $video->toArray()], 201);
        $this->assertTrue($response->json('data.opened'));
        $this->assertApiResource($response, VideoResource::class, $video);

        $response = $this->json('POST', route('videos.store'), [
            'title' => 'test',
            'description' => 'test',
            'year_launched' => 2015,
            'rating' => Video::RATING_LIST[4],
            'duration' => 50,
            'opened' => false,
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id],
        ]);
        $this->assertFalse($response->json('data.opened'));
    }

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($categoriesId);
        $genreId = $genre->id;
        $sendData = [
            'title' => 'test',
            'description' => 'test',
            'year_launched' => 2015,
            'rating' => Video::RATING_LIST[4],
            'duration' => 50,
            'opened' => false,
            'categories_id' => [$categoriesId[0]],
            'genres_id' => [$genreId],
        ];
        $response = $this->json('POST', route('videos.store'), $sendData);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[0],
            'video_id' => $response->json('data.id')
        ]);

        $response = $this->json('PUT', route('videos.update', ['video' => $response->json('data.id')]), ['title' => 'test',
            'description' => 'test',
            'year_launched' => 2015,
            'rating' => Video::RATING_LIST[4],
            'duration' => 50,
            'opened' => false,
            'genres_id' => [$genreId],
            'categories_id' => [$categoriesId[1], $categoriesId[2]]
        ]);
        $this->assertDatabaseMissing('category_video', [
            'category_id' => $categoriesId[0],
            'video_id' => $response->json('data.id')
        ]);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[1],
            'video_id' => $response->json('data.id')
        ]);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[2],
            'video_id' => $response->json('data.id')
        ]);
    }

    public function testSyncGenres()
    {
        $genres = factory(Genre::class, 3)->create();
        $genresId = $genres->pluck('id')->toArray();
        $categoryId = factory(Category::class)->create()->id;
        $genres->each(function ($genre) use ($categoryId) {
            $genre->categories()->sync($categoryId);
        });

        $sendData = [
            'title' => 'test',
            'description' => 'test',
            'year_launched' => 2015,
            'rating' => Video::RATING_LIST[4],
            'duration' => 50,
            'opened' => false,
            'categories_id' => [$categoryId],
            'genres_id' => [$genresId[0]],
        ];
        $response = $this->json('POST', route('videos.store'), $sendData);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[0],
            'video_id' => $response->json('data.id')
        ]);

        $response = $this->json('PUT', route('videos.update', ['video' => $response->json('data.id')]), ['title' => 'test',
            'description' => 'test',
            'year_launched' => 2015,
            'rating' => Video::RATING_LIST[4],
            'duration' => 50,
            'opened' => false,
            'genres_id' => [$genresId[1], $genresId[2]],
            'categories_id' => [$categoryId]
        ]);
        $this->assertDatabaseMissing('genre_video', [
            'genre_id' => $genresId[0],
            'video_id' => $response->json('data.id')
        ]);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[1],
            'video_id' => $response->json('data.id')
        ]);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[2],
            'video_id' => $response->json('data.id')
        ]);
    }

    public function testStoreWithTransactionFail()
    {
        try {
            Video::create([
                'title' => 'test',
                'description' => 'test',
                'year_launched' => 2015,
                'rating' => Video::RATING_LIST[4],
                'duration' => 50,
                'opened' => false,
                'categories_id' => [0],
            ]);
        } catch (QueryException $e) {
            $this->assertCount(0, Video::all());
        }
    }

    public function testUpdateWithTransactionFail()
    {
        $video = factory(Video::class)->create();
        $title = $video->title;
        try {
            $video->update([
                'title' => 'test',
                'description' => 'test',
                'year_launched' => 2015,
                'rating' => Video::RATING_LIST[4],
                'duration' => 60,
                'opened' => true,
                'categories_id' => [0],
            ]);
        } catch (QueryException $e) {
            $this->assertDatabaseHas('videos', ['title' => $title]);
        }
    }

    public function testUpdate()
    {
        $category_1 = factory(Category::class)->create([
            'name' => 'test 1'
        ]);
        $genre_1 = factory(Genre::class)->create([
            'name' => 'test 1',
        ]);
        $genre_1->categories()->attach([$category_1->id]);
        $response = $this->json('POST', route('videos.store'), [
            'title' => 'test',
            'description' => 'test',
            'year_launched' => 2015,
            'rating' => Video::RATING_LIST[4],
            'duration' => 50,
            'opened' => false,
            'categories_id' => [$category_1->id],
            'genres_id' => [$genre_1->id],
        ]);
        $video = Video::find($response->json('data.id'));
        $category_2 = factory(Category::class)->create([
            'name' => 'test 2'
        ]);
        $genre_2 = factory(Genre::class)->create([
            'name' => 'test 2',
        ]);
        $genre_2->categories()->attach([$category_2->id]);
        $response = $this->json('PUT', route('videos.update', ['video' => $video->id]), [
            'title' => 'test up',
            'description' => 'test up',
            'year_launched' => 1999,
            'rating' => Video::RATING_LIST[3],
            'duration' => 90,
            'opened' => true,
            'categories_id' => [$category_2->id],
            'genres_id' => [$genre_2->id],
        ]);
        $video = Video::find($response->json('data.id'));
        $this->assertOk($response, ['data' => $video->toArray()]);
        $response->assertJsonFragment([
            'opened' => true
        ]);
        $this->assertApiResource($response, VideoResource::class, $video);
    }

    public function testDelete()
    {
        $video = factory(Video::class)->create([
            'title' => 'test up',
            'description' => 'test up',
            'year_launched' => 1999,
            'rating' => '14',
            'duration' => 90,
            'opened' => true,
        ]);
        $video->refresh();
        $id = $video->id;
        $response = $this->json('DELETE', route('videos.destroy', ['video' => $id]));
        $response->assertStatus(204);
        $this->assertNull(Video::find($id));
        $this->assertNotNull(Video::withTrashed()->find($id));

        $response = $this->json('DELETE', route('videos.destroy', ['video' => $id]));
        $response->assertStatus(404);
    }

}
