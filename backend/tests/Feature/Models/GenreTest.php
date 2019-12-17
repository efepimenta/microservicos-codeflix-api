<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class GenreTest extends TestCase
{

    use DatabaseMigrations;

    public function testCreateNewGenre()
    {
        factory(Genre::class, 1)->create();
        $genres = Genre::all();
        $this->assertCount(1, $genres);

        $keys = array_keys($genres->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            ['id', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at'],
            $keys
        );
    }

    public function testCreateGenre()
    {
        $genre = Genre::create([
            'name' => 'genero de teste'
        ]);
        $genre->refresh();
        $this->assertEquals('genero de teste', $genre->name);
        $this->assertTrue($genre->is_active);
        $this->assertTrue(Uuid::isValid($genre->id));

        $genre = Genre::create([
            'name' => 'genero de teste',
            'is_active' => false
        ]);
        $genre->refresh();
        $this->assertFalse($genre->is_active);
        $this->assertTrue(Uuid::isValid($genre->id));
    }

    public function testEditGenre()
    {
        $genre = factory(Genre::class)->create([
            'is_active' => true
        ])->first();
        $data = [
            'name' => 'name update',
            'is_active' => true
        ];
        $genre->update($data);
        foreach ($data as $key => $value) {
            $this->assertEquals($value, $genre->{$key});
        }
    }

    public function testDeleteGenre()
    {
        $genres = factory(Genre::class, 5)->create();
        $count = 5;
        foreach ($genres as $genre) {
            $count--;
            $id = $genre->id;
            $this->assertTrue($genre->delete($id));
            $this->assertCount($count, Genre::all());
        }
        $this->assertCount(0, Genre::all());
    }
}
