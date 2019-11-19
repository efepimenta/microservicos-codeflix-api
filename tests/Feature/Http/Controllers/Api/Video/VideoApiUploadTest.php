<?php


namespace Tests\Feature\Http\Controllers\Api\Video;


use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Http\UploadedFile;
use Tests\Exceptions\TestException;
use Tests\Types\InvalidationTypes;

class VideoApiUploadTest extends BaseVideoApiTest
{

    public function testInvalidateVideoFileStore()
    {
        $file = UploadedFile::fake()->create("teste.mp3");
        $category = factory(Category::class)->create([
            'name' => 'test 1'
        ]);
        $genre = factory(Genre::class)->create([
            'name' => 'test 1'
        ]);
        $genre->categories()->sync([$category->id]);

        $this->assertInvalidationInStoreAction(route('videos.store'), [
            'title' => 'test',
            'description' => 'test',
            'year_launched' => 2015,
            'rating' => Video::RATING_LIST[0],
            'duration' => 50,
            'opened' => true,
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id],
            'video_file' => $file
        ], [
            new InvalidationTypes('video_file', 'mimetypes', ['values' => 'video/mp4']),
        ]);

        $file = UploadedFile::fake()->create("teste.mp4")->size(13);
        $this->assertInvalidationInStoreAction(route('videos.store'), [
            'title' => 'test',
            'description' => 'test',
            'year_launched' => 2015,
            'rating' => Video::RATING_LIST[0],
            'duration' => 50,
            'opened' => true,
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id],
            'video_file' => $file
        ], [
            new InvalidationTypes('video_file', 'max.file', ['max' => 12]),
        ]);
    }

    public function testInvalidateVideoFileUpdate()
    {
        $file = UploadedFile::fake()->create("teste.mp3");
        $category = factory(Category::class)->create([
            'name' => 'test 1'
        ]);
        $genre = factory(Genre::class)->create([
            'name' => 'test 1'
        ]);
        $genre->categories()->sync([$category->id]);

        $video = factory(Video::class)->create();
        $this->assertInvalidationInUpdateAction(route('videos.update', ['video' => $video->id]), [
            'video_file' => $file
        ], [
            new InvalidationTypes('video_file', 'mimetypes', ['values' => 'video/mp4']),
        ]);

        $file = UploadedFile::fake()->create("teste.mp4")->size(13);
        $this->assertInvalidationInUpdateAction(route('videos.update', ['video' => $video->id]), [
            'video_file' => $file
        ], [
            new InvalidationTypes('video_file', 'max.file', ['max' => 12]),
        ]);
    }

    public function testValidateVideoFileStore()
    {
        \Storage::fake();
        $file = UploadedFile::fake()->create("teste.mp4")->size(12);
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
            'video_file' => $file,
        ]);
        $video = Video::find($response->json('id'));
        \Storage::assertExists("{$video->id}/{$file->hashName()}");
    }

}
