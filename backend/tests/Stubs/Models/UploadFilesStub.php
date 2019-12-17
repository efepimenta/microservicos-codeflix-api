<?php


namespace Tests\Stubs\Models;


use App\Models\Traits\UploadFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class UploadFilesStub extends Model
{

    use UploadFiles;

    public static $fileFields = ['file1', 'file2'];

    protected $table = 'upload_file_stub';
    protected $fillable = ['name', 'banner', 'file1', 'file2'];

    public static function createTable()
    {
        \Schema::create('upload_file_stub', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('banner')->nullable();
            $table->string('file1')->nullable();
            $table->string('file2')->nullable();
            $table->timestamps();
        });
    }

    public static function dropTable()
    {
        \Schema::dropIfExists('upload_file_stub');
    }

    protected function uploadDir()
    {
        return '1';
    }
}
