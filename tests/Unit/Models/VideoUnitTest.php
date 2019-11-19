<?php

namespace Tests\Unit\Models;

use App\Models\Traits\UploadFiles;
use App\Models\Traits\Uuid;
use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VideoUnitTest extends TestCase
{

    private $video;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = new Video();
    }


    public function testIfClassIsModel(){
        $this->assertInstanceOf(Model::class, new Video());
    }

    public function testFillable()
    {
        $fillable = ['title', 'description', 'year_launched', 'opened', 'rating', 'duration', 'video_file', 'thumb_file'];
        $this->assertEquals($fillable, $this->video->getFillable());
    }

    public function testCasts()
    {
        $casts = [
            'id' => 'string',
            'title' => 'string',
            'description' => 'string',
            'opened' => 'boolean',
            'year_launched' => 'integer',
            'rating' => 'string',
            'duration' => 'integer',
        ];
        $this->assertEquals($casts, $this->video->getCasts());
    }

    public function testDates()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        foreach ($dates as $date) {
            $this->assertContains($date, $this->video->getDates());
        }
        $this->assertCount(count($dates), $this->video->getDates());
    }

    public function testIncrementig()
    {
        $this->assertFalse($this->video->getIncrementing());
    }

    public function testIfUseTraits() {
        $traits = [
            Uuid::class, SoftDeletes::class, UploadFiles::class
        ];
        $genreTraits = array_keys(class_uses(Video::class));
        $this->assertEqualsCanonicalizing($traits, $genreTraits);
    }

}
