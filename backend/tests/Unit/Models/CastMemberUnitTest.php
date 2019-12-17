<?php

namespace Tests\Unit\Models;

use App\Models\CastMember;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CastMemberUnitTest extends TestCase
{

    private $castMember;

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = new CastMember();
    }


    public function testIfClassIsModel(){
        $this->assertInstanceOf(Model::class, new CastMember());
    }

    public function testFillable()
    {
        $fillable = ['name', 'type', 'is_active'];
        $this->assertEquals($fillable, $this->castMember->getFillable());
    }

    public function testCasts()
    {
        $casts = ['id' => 'string', 'is_active' => 'boolean'];
        $this->assertEquals($casts, $this->castMember->getCasts());
    }

    public function testDates()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        foreach ($dates as $date) {
            $this->assertContains($date, $this->castMember->getDates());
        }
        $this->assertCount(count($dates), $this->castMember->getDates());
    }

    public function testIncrementig()
    {
        $this->assertFalse($this->castMember->getIncrementing());
    }

    public function testIfUseTraits() {
        $traits = [
            Uuid::class, SoftDeletes::class
        ];
        $castMemberTraits = array_keys(class_uses(CastMember::class));
        $this->assertEqualsCanonicalizing($traits, $castMemberTraits);
    }

}
