<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Http\Resources\GenreResource;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\TestApiResources;
use Tests\Traits\TestValidations;
use Tests\Types\InvalidationTypes;

class GenreApiTest extends TestCase
{

    use DatabaseMigrations;

    use TestValidations, TestApiResources;

    public function testIndex()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.index'));
        $this->assertOk($response, ['data' => [$genre->toArray()]]);

        $response->assertJsonStructure([
            'data' => [],
            'links' => [],
            'meta' => [],
        ])->assertJson([
            'meta' => ['per_page' => 15]
        ]);
        $resource = GenreResource::collection(collect([$genre]));

        $json = $resource->response();
        $response->assertJson($json->getData(true));
    }

    public function testShow()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.show', ['genre' => $genre->id]));
        $this->assertOk($response, ['data' => $genre->toArray()]);

        $genre = Genre::find($response->json('data.id'));
        $this->assertApiResource($response, GenreResource::class, $genre);
    }

    public function testInvalidationData()
    {
        $this->assertInvalidationInStoreAction(route('genres.store'), [], [
            new InvalidationTypes('name', 'required'),
            new InvalidationTypes('categories_id', 'required'),
        ]);

        $this->assertInvalidationInStoreAction(route('genres.store'), [
            'name' => str_repeat('a', 101),
            'is_active' => 'a'
        ], [
            new InvalidationTypes('name', 'max.string', ['max' => 100]),
            new InvalidationTypes('is_active', 'boolean'),
        ]);

        $genre = factory(Genre::class)->create();
        $this->assertInvalidationInUpdateAction(route('genres.update', ['genre' => $genre->id]), [
            'name' => str_repeat('a', 101),
            'is_active' => 'a',
            'categories_id' => 'test'
        ], [
            new InvalidationTypes('name', 'max.string', ['max' => 100]),
            new InvalidationTypes('is_active', 'boolean'),
            new InvalidationTypes('categories_id', 'array'),
        ]);
    }

    public function testStore()
    {
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test 1',
        ]);
        $category = Category::find($response->json('data.id'));
        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test',
            'categories_id' => [$category->id]
        ]);
        $genre = Genre::find($response->json('data.id'));
        $this->assertOk($response, ['data' => $genre->toArray()], 201);
        $this->assertTrue($response->json('data.is_active'));
        $this->assertApiResource($response, GenreResource::class, $genre);

        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test',
            'is_active' => false,
            'categories_id' => [$category->id]
        ]);
        $this->assertFalse($response->json('data.is_active'));
    }

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $sendData = ['name' => 'test', 'categories_id' => [$categoriesId[0]]];
        $response = $this->json('POST', route('genres.store'), $sendData);
        $this->assertDatabaseHas('category_genre', [
           'category_id' => $categoriesId[0],
           'genre_id' => $response->json('data.id')
        ]);
        $sendData = ['name' => 'test', 'categories_id' => [$categoriesId[1], $categoriesId[2]]];
        $response = $this->json('PUT', route('genres.update', ['genre' =>$response->json('data.id')]), $sendData);
        $this->assertDatabaseMissing('category_genre', [
            'category_id' => $categoriesId[0],
            'genre_id' => $response->json('data.id')
        ]);
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesId[1],
            'genre_id' => $response->json('data.id')
        ]);
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesId[2],
            'genre_id' => $response->json('data.id')
        ]);
    }

    public function testStoreWithTransactionFail()
    {
        $controller = $this->makeObjectsForTransactionFailTests();
        $request = \Mockery::mock(Request::class);
        try {
            $controller->store($request);
        } catch (TestException $e) {
            $this->assertCount(0, Genre::all());
        }
    }

    public function testUpdate()
    {
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test 1',
        ]);
        $category = Category::find($response->json('data.id'));
        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test',
            'is_active' => false,
            'categories_id' => [$category->id]
        ]);
        $genre = Genre::find($response->json('data.id'));
        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), [
            'name' => 'updating',
            'is_active' => true,
            'categories_id' => [$category->id]
        ]);
        $genre = Genre::find($response->json('data.id'));
        $this->assertOk($response, ['data' => $genre->toArray()]);
        $response->assertJsonFragment([
            'is_active' => true
        ]);
        $this->assertApiResource($response, GenreResource::class, $genre);
    }

    public function testUpdateWithTransactionFail()
    {
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test 1'
        ]);
        $category = Category::find($response->json('data.id'));
        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test 1',
            'categories_id' => [$category->id]
        ]);
        $genre = Genre::find($response->json('data.id'));
        $controller = $this->makeObjectsForTransactionFailTests();
        $request = \Mockery::mock(Request::class);
        try {
            $controller->update($request, ['video' => $genre->id]);
        } catch (TestException $e) {
            $this->assertEquals($category->id, $genre->categories[0]->id);
        }
    }

    public function testDelete()
    {
        $genre = factory(Genre::class)->create([
            'name' => 'deleting'
        ]);
        $genre->refresh();
        $id = $genre->id;
        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $id]));
        $response->assertStatus(204);
        $this->assertNull(Genre::find($id));
        $this->assertNotNull(Genre::withTrashed()->find($id));

        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $id]));
        $response->assertStatus(404);
    }

    private function makeObjectsForTransactionFailTests() {
        $genre = [
            'name' => 'test 1',
            'categories_id' => [Uuid::uuid4()]
        ];
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($genre);
        $controller->shouldReceive('rulesStore')
            ->withAnyArgs()
            ->andReturn([]);
        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());
        return $controller;
    }

}
