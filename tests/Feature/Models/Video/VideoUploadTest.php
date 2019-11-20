<?php


namespace Tests\Feature\Models\Video;


use App\Models\Video;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Http\UploadedFile;
use Tests\Exceptions\TestException;

class VideoUploadTest extends BaseVideoTestCase
{

    public function testCreateWithFiles()
    {
        \Storage::fake();
        $video = Video::create(
            $this->videoExample + [
                'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                'video_file' => UploadedFile::fake()->create('video.mp4'),
                'banner_file' => UploadedFile::fake()->image('banner.png'),
                'trailer_file' => UploadedFile::fake()->create('trailer.mp4'),
            ]
        );
        \Storage::assertExists("{$video->id}/{$video->thumb_file}");
        \Storage::assertExists("{$video->id}/{$video->video_file}");
        \Storage::assertExists("{$video->id}/{$video->banner_file}");
        \Storage::assertExists("{$video->id}/{$video->trailer_file}");
    }

    public function testDeleteFiles()
    {
        \Storage::fake();
        $video = Video::create($this->videoExample);
        $file1 = UploadedFile::fake()->image('thumb2.jpg');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $video->uploadFiles([$file1, $file2]);
        $video->deleteOldFiles();
        $this->assertCount(2, \Storage::allFiles());

        $video->oldFiles = [$file1->hashName()];
        $video->deleteOldFiles();

        \Storage::assertMissing("{$video->id}/{$file1->hashName()}");
        \Storage::assertExists("{$video->id}/{$file2->hashName()}");
    }

    public function testCreateWithRollbackFiles()
    {
        \Storage::fake();
        \Event::listen(TransactionCommitted::class, function () {
            throw new TestException();
        });
        $hasError = false;
        try {
            Video::create(
                $this->videoExample + [
                    'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                    'video_file' => UploadedFile::fake()->create('video.mp4'),
                    'banner_file' => UploadedFile::fake()->create('banner.mp4'),
                    'trailer_file' => UploadedFile::fake()->create('trailer.mp4'),
                ]
            );
        } catch (TestException $e) {
            $this->assertCount(0, \Storage::allFiles());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testDeleteOldFiles()
    {
        \Storage::fake();
        $video = Video::create($this->videoExample);
        $file1 = UploadedFile::fake()->image('thumb2.jpg');
        $video->uploadFile($file1);
        $video->deleteFile($file1->hashName());
        \Storage::assertMissing($file1->hashName());

        $file1 = UploadedFile::fake()->image('thumb2.jpg');
        $video->uploadFile($file1);
        $video->deleteFile($file1->hashName());
        \Storage::assertMissing($file1->hashName());
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $video = Video::create($this->videoExample);
        $thumb_file = UploadedFile::fake()->image('thumb1.jpg');
        $video_file = UploadedFile::fake()->create('video2.mp4');
        $banner_file = UploadedFile::fake()->create('banner.mp4');
        $trailer_file = UploadedFile::fake()->create('trailer.mp4');
        $video->update($this->videoExample + [
                'thumb_file' => $thumb_file,
                'video_file' => $video_file,
                'banner_file' => $banner_file,
                'trailer_file' => $trailer_file,
            ]);
        \Storage::assertExists("{$video->id}/{$thumb_file->hashName()}");
        \Storage::assertExists("{$video->id}/{$video_file->hashName()}");
        \Storage::assertExists("{$video->id}/{$banner_file->hashName()}");
        \Storage::assertExists("{$video->id}/{$trailer_file->hashName()}");

        $new_video = UploadedFile::fake()->create('video3.mp4');
        $video->update($this->videoExample + [
                'video_file' => $new_video,
            ]);
        \Storage::assertExists("{$video->id}/{$thumb_file->hashName()}");
        \Storage::assertMissing("{$video->id}/{$video_file->hashName()}");
        \Storage::assertExists("{$video->id}/{$banner_file->hashName()}");
        \Storage::assertExists("{$video->id}/{$trailer_file->hashName()}");
        \Storage::assertExists("{$video->id}/{$new_video->hashName()}");
    }

    public function testUpdateWithRollbackFiles()
    {
        \Storage::fake();
        $video = factory(Video::class)->create();
        \Event::listen(TransactionCommitted::class, function () {
            throw new TestException();
        });
        $hasError = false;
        try {
            $video->update(
                $this->videoExample + [
                    'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                    'video_file' => UploadedFile::fake()->create('video.mp4'),
                ]
            );
        } catch (TestException $e) {
            $this->assertCount(0, \Storage::allFiles());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }
}
