<?php

namespace Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class VideoStub extends Model
{

    protected $table = 'videos_stub';
    protected $fillable = ['title', 'description', 'year_launched', 'opened', 'rating', 'duration'];

    public static function createTable()
    {
        \Schema::create('videos_stub', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('description');
            $table->smallInteger('year_launched');
            $table->string('rating', 3);
            $table->smallInteger('duration');
            $table->boolean('opened');
            $table->timestamps();
        });
    }

    public static function dropTable()
    {
        \Schema::dropIfExists('videos_stub');
    }
}
