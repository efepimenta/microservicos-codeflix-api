<?php

namespace Tests\Feature\Models;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class CastMemberTest extends TestCase
{

    use DatabaseMigrations;

    public function testCreateNewCastMember()
    {
        factory(CastMember::class, 1)->create();
        $genres = CastMember::all();
        $this->assertCount(1, $genres);

        $keys = array_keys($genres->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            ['id', 'name', 'type', 'is_active', 'created_at', 'updated_at', 'deleted_at'],
            $keys
        );
    }

    public function testCreateCastMember()
    {
        $genre = CastMember::create([
            'name' => 'genero de teste',
            'type' => 1
        ]);
        $genre->refresh();
        $this->assertEquals('genero de teste', $genre->name);
        $this->assertTrue($genre->is_active);
        $this->assertTrue(Uuid::isValid($genre->id));

        $genre = CastMember::create([
            'name' => 'genero de teste',
            'type' => 1,
            'is_active' => false
        ]);
        $genre->refresh();
        $this->assertFalse($genre->is_active);
        $this->assertTrue(Uuid::isValid($genre->id));
    }

    public function testEditCastMember()
    {
        $genre = factory(CastMember::class)->create([
            'is_active' => true
        ])->first();
        $data = [
            'name' => 'name update',
            'type' => 1,
            'is_active' => true
        ];
        $genre->update($data);
        foreach ($data as $key => $value) {
            $this->assertEquals($value, $genre->{$key});
        }
    }

    public function testDeleteCastMember()
    {
        $genres = factory(CastMember::class, 5)->create();
        $count = 5;
        foreach ($genres as $genre) {
            $count--;
            $id = $genre->id;
            $this->assertTrue($genre->delete($id));
            $this->assertCount($count, CastMember::all());
        }
        $this->assertCount(0, CastMember::all());
    }
}
