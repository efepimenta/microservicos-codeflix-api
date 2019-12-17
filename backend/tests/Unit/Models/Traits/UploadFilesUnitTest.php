<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UploadFilesUnitTest extends TestCase
{

    private $obj;

    protected function setUp(): void
    {
        parent::setUp();
        UploadFilesStub::dropTable();
        UploadFilesStub::createTable();
        $this->obj = new UploadFilesStub();
    }


    public function testUploadFile()
    {
        \Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);
        \Storage::assertExists("1/{$file->hashName()}");
    }

    public function testUploadFiles()
    {
        \Storage::fake();
        $file1 = UploadedFile::fake()->create('video.mp4');
        $file2 = UploadedFile::fake()->create('video.mp3');
        $this->obj->uploadFiles([$file1, $file2]);
        \Storage::assertExists("1/{$file2->hashName()}");
        \Storage::assertExists("1/{$file2->hashName()}");
    }

    public function testDeleteFile()
    {
        \Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);
        \Storage::assertExists("1/{$file->hashName()}");
        $this->obj->deleteFile($file->hashName());
        \Storage::assertMissing("1/{$file->hashName()}");

        \Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);
        \Storage::assertExists("1/{$file->hashName()}");
        $this->obj->deleteFile($file);
        \Storage::assertMissing("1/{$file->hashName()}");
    }

    public function testDeleteFiles()
    {
        \Storage::fake();
        $file1 = UploadedFile::fake()->create('video.mp4');
        $file2 = UploadedFile::fake()->create('video.mp3');
        $this->obj->uploadFiles([$file1, $file2]);
        \Storage::assertExists("1/{$file2->hashName()}");
        \Storage::assertExists("1/{$file2->hashName()}");

        $this->obj->deleteFiles([$file1->hashName(), $file2]);
        \Storage::assertMissing("1/{$file1->hashName()}");
        \Storage::assertMissing("1/{$file2->hashName()}");
    }

    public function testExtractFiles()
    {
        $attributes = [];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(0, $attributes);
        $this->assertCount(0, $files);

        $attributes = ['name' => 'teste'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(1, $attributes);
        $this->assertEquals(['name' => 'teste'], $attributes);
        $this->assertCount(0, $files);

        $attributes = ['name' => 'teste', 'banner' => 'teste'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(2, $attributes);
        $this->assertEquals(['name' => 'teste', 'banner' => 'teste'], $attributes);
        $this->assertCount(0, $files);

        $file1 = UploadedFile::fake()->create('video1.mp4');
        $attributes = ['file1' => $file1, 'banner' => 'teste'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(2, $attributes);
        $this->assertEquals(['file1' => $file1->hashName(), 'banner' => 'teste'], $attributes);
        $this->assertEquals([$file1], $files);
        $this->assertCount(1, $files);

        $file2 = UploadedFile::fake()->create('video2.mp4');
        $attributes = ['file1' => $file1, 'banner' => 'teste', 'file2' => $file2];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(3, $attributes);
        $this->assertEquals([
            'file1' => $file1->hashName(),
            'banner' => 'teste',
            'file2' => $file2->hashName(),
        ], $attributes);
        $this->assertEquals([$file1, $file2], $files);
        $this->assertCount(2, $files);

    }

    public function testMakeOldFilesOnSave() {
        $this->obj->fill([
            'name' => 'test',
            'file1' => 'test.mp4',
            'file2' => 'test2.mp4',
        ]);
        $this->obj->save();
        $this->assertCount(0, $this->obj->oldFiles);

        $this->obj->update([
           'name' => 'test1_name',
           'file2' => 'test3.mp4'
        ]);
        $this->assertEqualsCanonicalizing(['test2.mp4'], $this->obj->oldFiles);
    }

    public function testMakeOldFilesWithNull() {
        $this->obj->fill([
            'name' => 'test'
        ]);
        $this->obj->save();
        $this->obj->update([
            'name' => 'test1_name',
            'file2' => 'test3.mp4'
        ]);
        $this->assertEqualsCanonicalizing([], $this->obj->oldFiles);
    }

}
