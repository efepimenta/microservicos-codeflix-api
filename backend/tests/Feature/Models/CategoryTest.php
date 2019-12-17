<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class CategoryTest extends TestCase
{

    use DatabaseMigrations;

    public function testCreateNewCategory()
    {
        factory(Category::class, 1)->create();
//        $category = Category::create([
//            'name' => 'categoria de teste'
//        ]);
        $categories = Category::all();
        $this->assertCount(1, $categories);

        $keys = array_keys($categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            ['id', 'name', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at'],
            $keys
        );
    }

    public function testCreateCategory()
    {
        $category = Category::create([
            'name' => 'categoria de teste'
        ]);
        $category->refresh();
        $this->assertEquals('categoria de teste', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);
        $this->assertTrue(Uuid::isValid($category->id));

        $category = Category::create([
            'name' => 'categoria de teste',
            'description' => null
        ]);
        $category->refresh();
        $this->assertNull($category->description);

        $category = Category::create([
            'name' => 'categoria de teste',
            'description' => 'null'
        ]);
        $category->refresh();
        $this->assertEquals('null', $category->description);
        $this->assertTrue(Uuid::isValid($category->id));

        $category = Category::create([
            'name' => 'categoria de teste',
            'is_active' => false
        ]);
        $category->refresh();
        $this->assertFalse($category->is_active);
        $this->assertTrue(Uuid::isValid($category->id));
    }

    public function testEditCategory()
    {
        $category = factory(Category::class)->create([
            'description' => 'test description',
            'is_active' => true
        ])->first();
        $data = [
            'name' => 'name update',
            'description' => 'test description update',
            'is_active' => true
        ];
        $category->update($data);
        foreach ($data as $key => $value) {
            $this->assertEquals($value, $category->{$key});
        }
    }

    public function testDeleteCategory()
    {
        $categories = factory(Category::class, 5)->create();
        $count = 5;
        foreach ($categories as $category) {
            $count--;
            $id = $category->id;
            $this->assertTrue($category->delete($id));
            $this->assertCount($count, Category::all());
        }
        $this->assertCount(0, Category::all());
    }
}
