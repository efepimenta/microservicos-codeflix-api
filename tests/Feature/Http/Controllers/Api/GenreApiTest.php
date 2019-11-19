<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\TestValidations;
use Tests\Types\InvalidationTypes;

class GenreApiTest extends TestCase
{

    use DatabaseMigrations;

    use TestValidations;

    public function testIndex()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.index'));
        $this->assertOk($response, [$genre->toArray()]);
    }

    public function testShow()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.show', ['genre' => $genre->id]));
        $this->assertOk($response, $genre->toArray());
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
        $category = Category::find($response->json('id'));
        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test',
            'categories_id' => [$category->id]
        ]);
        $genre = Genre::find($response->json('id'));
        $this->assertOk($response, $genre->toArray(), 201);
        $this->assertTrue($response->json('is_active'));

        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test',
            'is_active' => false,
            'categories_id' => [$category->id]
        ]);
        $this->assertFalse($response->json('is_active'));
    }

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $sendData = ['name' => 'test', 'categories_id' => [$categoriesId[0]]];
        $response = $this->json('POST', route('genres.store'), $sendData);
        $this->assertDatabaseHas('category_genre', [
           'category_id' => $categoriesId[0],
           'genre_id' => $response->json('id')
        ]);
        $sendData = ['name' => 'test', 'categories_id' => [$categoriesId[1], $categoriesId[2]]];
        $response = $this->json('PUT', route('genres.update', ['genre' =>$response->json('id')]), $sendData);
        $this->assertDatabaseMissing('category_genre', [
            'category_id' => $categoriesId[0],
            'genre_id' => $response->json('id')
        ]);
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesId[1],
            'genre_id' => $response->json('id')
        ]);
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesId[2],
            'genre_id' => $response->json('id')
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
        $category = Category::find($response->json('id'));
        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test',
            'is_active' => false,
            'categories_id' => [$category->id]
        ]);
        $genre = Genre::find($response->json('id'));
        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), [
            'name' => 'updating',
            'is_active' => true,
            'categories_id' => [$category->id]
        ]);
        $genre = Genre::find($response->json('id'));
        $this->assertOk($response, $genre->toArray());
        $response->assertJsonFragment([
            'is_active' => true
        ]);
    }

    public function testUpdateWithTransactionFail()
    {
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test 1'
        ]);
        $category = Category::find($response->json('id'));
        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test 1',
            'categories_id' => [$category->id]
        ]);
        $genre = Genre::find($response->json('id'));
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
