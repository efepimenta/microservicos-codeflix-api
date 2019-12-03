<?php


namespace Tests\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Resources\Json\JsonResource;

trait TestApiResources
{
    protected function assertApiResource(TestResponse $response, string $resourceClass, Model $model) {
        $json = (new $resourceClass($model))->response();
        $this->assertEquals($response->content(), $json->content());
        $response->assertJson($json->getData(true));
    }
}
