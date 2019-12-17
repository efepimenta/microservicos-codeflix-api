<?php


namespace Tests\Feature\Rules;


use App\Models\Category;
use App\Models\Genre;
use App\Rules\GenresHasCategoriesRules;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class GenreHasCategoriesTest extends TestCase
{

    use DatabaseMigrations;

    private $categories;
    private $genres;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categories = factory(Category::class, 4)->create();
        $this->genres = factory(Genre::class, 3)->create();

        $this->genres[0]->categories()->sync([
            $this->categories[0]->id,
            $this->categories[1]->id,
        ]);
        $this->genres[1]->categories()->sync([
            $this->categories[2]->id
        ]);
    }

    public function testPassesIsValid()
    {
        $rule = new GenresHasCategoriesRules([$this->categories[2]->id]);
        $isvalid = $rule->passes('', [$this->genres[1]->id]);
        $this->assertTrue($isvalid);

        $rule = new GenresHasCategoriesRules([
            $this->categories[0]->id,
            $this->categories[1]->id
        ]);
        $isvalid = $rule->passes('', [$this->genres[0]->id]);
        $this->assertTrue($isvalid);

        $rule = new GenresHasCategoriesRules([
            $this->categories[0]->id,
            $this->categories[1]->id,
            $this->categories[2]->id,
        ]);
        $isvalid = $rule->passes('', [
            $this->genres[0]->id,
            $this->genres[1]->id
        ]);
        $this->assertTrue($isvalid);
    }

    public function testPassesIsNotValid()
    {
        $rule = new GenresHasCategoriesRules([$this->categories[0]->id]);
        $isvalid = $rule->passes('', [
            $this->genres[0]->id,
            $this->genres[1]->id
        ]);
        $this->assertFalse($isvalid);

        $rule = new GenresHasCategoriesRules([$this->categories[3]->id]);
        $isvalid = $rule->passes('', [
            $this->genres[2]->id
        ]);
        $this->assertFalse($isvalid);
    }

}
