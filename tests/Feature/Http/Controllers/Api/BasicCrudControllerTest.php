<?php
declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Tests\Stubs\Controllers\CastMemberControllerStub;
use Tests\Stubs\Controllers\CategoryControllerStub;
use Tests\Stubs\Controllers\GenreControllerStub;
use Tests\Stubs\Controllers\VideoControllerStub;
use Tests\Stubs\Models\CastMemberStub;
use Tests\Stubs\Models\CategoryStub;
use Tests\Stubs\Models\GenreStub;
use Tests\Stubs\Models\VideoStub;
use Tests\TestCase;

class BasicCrudControllerTest extends TestCase
{
    /**@var CategoryControllerStub */
    private $categoryController;
    /** @var GenreControllerStub */
    private $genreController;
    /** @var CastMemberControllerStub */
    private $castMemberController;
    /** @var VideoControllerStub */
    private $videoController;
    private $videoData = [
        'title' => 'test up',
        'description' => 'test up',
        'year_launched' => 1999,
        'rating' => '14',
        'duration' => 90,
        'opened' => true,];

    protected function setUp(): void
    {
        parent::setUp();
        CategoryStub::dropTable();
        CategoryStub::createTable();
        $this->categoryController = new CategoryControllerStub();

        GenreStub::dropTable();
        GenreStub::createTable();
        $this->genreController = new GenreControllerStub();

        CastMemberStub::dropTable();
        CastMemberStub::createTable();
        $this->castMemberController = new CastMemberControllerStub();

        VideoStub::dropTable();
        VideoStub::createTable();
        $this->videoController = new VideoControllerStub();
    }

    protected function tearDown(): void
    {
        CategoryStub::dropTable();
        GenreStub::dropTable();
        CastMemberStub::dropTable();
        VideoStub::dropTable();
        parent::tearDown();
    }

    private function autoTestIfFindOrFailFetchModel(Model $obj, BasicCrudController $controller, $concreteClass)
    {
        $reflectionClass = new \ReflectionClass(BasicCrudController::class);
        $reflectionMethodFindOrFail = $reflectionClass->getMethod('findOrFail');
        $reflectionMethodFindOrFail->setAccessible(true);
        $result = $reflectionMethodFindOrFail->invokeArgs($controller, [$obj->id]);
        $this->assertInstanceOf($concreteClass, $result);
    }

    private function repeatTestInvalidationInStore(array $dataToReturn, BasicCrudController $controller, $concreteClass)
    {
        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn($dataToReturn);
        $obj = $controller->store($request);
        $this->assertEquals((new $concreteClass)::find(1)->toArray(), $obj->toArray());
        $this->assertEquals([$obj->toArray()], $controller->index()->toArray());
    }

    private function repeatTestInvalidationInUpdate(array $dataToReturn, BasicCrudController $controller, $concreteClass)
    {
        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->twice()
            ->andReturn($dataToReturn);
        $controller->store($request);
        $obj = $controller->update($request, 1);
        $this->assertEquals((new $concreteClass)::find(1)->toArray(), $obj->toArray());
        $this->assertEquals([$obj->toArray()], $controller->index()->toArray());
    }

    private function repeatTestInvalidationInDelete(array $dataToReturn, BasicCrudController $controller, $concreteClass)
    {
        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn($dataToReturn);
        $obj = $controller->store($request);
        $obj->destroy(1);
        $this->assertCount(0, (new $concreteClass)::all());
    }

    public function testIfFindOrFailFetchModel()
    {
        $category = CategoryStub::create(['name' => 'test name', 'description' => 'test description']);
        $this->autoTestIfFindOrFailFetchModel($category, $this->categoryController, CategoryStub::class);

        $genre = GenreStub::create(['name' => 'test name']);
        $this->autoTestIfFindOrFailFetchModel($genre, $this->genreController, GenreStub::class);

        $castMember = CastMemberStub::create(['name' => 'test name', 'type' => 1]);
        $this->autoTestIfFindOrFailFetchModel($castMember, $this->castMemberController, CastMemberStub::class);

        $video = VideoStub::create($this->videoData);
        $this->autoTestIfFindOrFailFetchModel($video, $this->videoController, VideoStub::class);
    }

    public function testIfFindOrFailThrowExceptionWhenIdInvalid()
    {
        $this->expectException(ModelNotFoundException::class);

        $reflectionClass = new \ReflectionClass(BasicCrudController::class);
        $reflectionMethodFindOrFail = $reflectionClass->getMethod('findOrFail');
        $reflectionMethodFindOrFail->setAccessible(true);
        $reflectionMethodFindOrFail->invokeArgs($this->categoryController, [0]);
    }

    public function testIndex()
    {
        $category = CategoryStub::create(['name' => 'test name', 'description' => 'test description']);
        $this->assertEquals([$category->toArray()], $this->categoryController->index()->toArray());

        $genre = GenreStub::create(['name' => 'test name']);
        $this->assertEquals([$genre->toArray()], $this->genreController->index()->toArray());

        $castMember = CastMemberStub::create(['name' => 'test name', 'type' => 2]);
        $this->assertEquals([$castMember->toArray()], $this->castMemberController->index()->toArray());

        $video = VideoStub::create($this->videoData);
        $this->assertEquals([$video->toArray()], $this->videoController->index()->toArray());
    }

    public function testInvalidationInStore()
    {
        $this->repeatTestInvalidationInStore(['name' => 'test name', 'description' => 'test description'], $this->categoryController, CategoryStub::class);
        $this->repeatTestInvalidationInStore(['name' => 'test name',], $this->genreController, GenreStub::class);
        $this->repeatTestInvalidationInStore(['name' => 'test name', 'type' => 1], $this->castMemberController, CastMemberStub::class);
        $this->repeatTestInvalidationInStore($this->videoData, $this->videoController, VideoStub::class);
    }

    public function testInvalidationInUpdate()
    {
        $this->repeatTestInvalidationInUpdate(['name' => 'test name', 'description' => 'test description'], $this->categoryController, CategoryStub::class);
        $this->repeatTestInvalidationInUpdate(['name' => 'test name',], $this->genreController, GenreStub::class);
        $this->repeatTestInvalidationInUpdate(['name' => 'test name', 'type' => 1], $this->castMemberController, CastMemberStub::class);
        $this->repeatTestInvalidationInUpdate($this->videoData, $this->videoController, VideoStub::class);
    }

    public function testInvalidationInDelete()
    {
        $this->repeatTestInvalidationInDelete(['name' => 'test name', 'description' => 'test description'], $this->categoryController, CategoryStub::class);
        $this->repeatTestInvalidationInDelete(['name' => 'test name',], $this->genreController, GenreStub::class);
        $this->repeatTestInvalidationInDelete(['name' => 'test name', 'type' => 1], $this->castMemberController, CastMemberStub::class);
        $this->repeatTestInvalidationInDelete($this->videoData, $this->videoController, VideoStub::class);
    }

}
