<?php

use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Seeder;

class VideoTableSeeder extends Seeder
{
    /**
     * @var Genre[]|\Illuminate\Database\Eloquent\Collection
     */
    private $allGenres;
    private $relations = [
        'genres_id' => [],
        'categories_id' => [],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dir = Storage::getDriver()->getAdapter()->getPathPrefix();
        \File::deleteDirectory($dir, true);

        $self = $this;
        $this->allGenres = Genre::all();
        \Illuminate\Database\Eloquent\Model::reguard();
        factory(Video::class, 100)
            ->make()
            ->each(function (Video $video) use ($self){
               $self->fetchRelations();
               Video::create(
                   array_merge(
                       $video->toArray(),
                       [
                           'thumb_file' => $self->getImageFile(),
                           'banner_file' => $self->getImageFile(),
                           'trailer_file' => $self->getVideoFile(),
                           'video_file' => $self->getVideoFile(),
                       ],
                       $self->relations
                   )
               );
            });
        \Illuminate\Database\Eloquent\Model::unguard();

//        $genres = Genre::all();
//        factory(Video::class, 100)->create()
//            ->each(function (Video $video) use ($genres) {
//                $subGenres = $genres->random(5)->load('categories');
//                $categoriesId = [];
//                foreach ($subGenres as $genre) {
//                    array_push($categoriesId, ...$genre->categories->pluck('id')->toArray());
//                }
//                $categoriesId = array_unique($categoriesId);
//                $video->categories()->attach($categoriesId);
//                $video->genres()->attach($subGenres->pluck('id')->toArray());
//            });
    }

    private function getImageFile()
    {
        return new \Illuminate\Http\UploadedFile(
            storage_path('faker/thumbs/Laravel Framework.png'),
            'Laravel Framework'
        );
    }

    private function getVideoFile()
    {
        return new \Illuminate\Http\UploadedFile(
            storage_path('faker/videos/01-como vao funcionar os uploads.mp4'),
            'faker/videos/01-como vao funcionar os uploads.mp4'
        );
    }

    private function fetchRelations()
    {
        $subGenres = $this->allGenres->random(5)->load('categories');
        $categoriesId = [];
        foreach ($subGenres as $genre) {
            array_push($categoriesId, ...$genre->categories->pluck('id')->toArray());
        }
        $categoriesId = array_unique($categoriesId);
        $genresId = $subGenres->pluck('id')->toArray();
        $this->relations['categories_id'] = $categoriesId;
        $this->relations['genres_id'] = $genresId;
    }
}
