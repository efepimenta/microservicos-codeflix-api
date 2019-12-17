<?php

declare(strict_types=1);

namespace Tests\Traits;


use Illuminate\Foundation\Testing\TestResponse;
use Tests\Types\InvalidationTypes;

trait TestValidations
{

    /**
     * @param TestResponse $response
     * @param json array $return
     * @param int $status
     */
    protected function assertOk(TestResponse $response, $return, int $status = 200)
    {
        $response
            ->assertStatus($status)
            ->assertJson($return);
    }

    /**
     * @param string $route
     * @param array $data
     * @param array $fields
     * @return TestResponse
     */
    protected function assertInvalidationInStoreAction(string $route, array $data, array $fields): TestResponse
    {
        return $this->executeAction('POST', $route, $data, $fields);
    }

    /**
     * @param string $route
     * @param array $data
     * @param array $fields
     * @return TestResponse
     */
    protected function assertInvalidationInUpdateAction(string $route, array $data, array $fields): TestResponse
    {
        return $this->executeAction('PUT', $route, $data, $fields);
    }

    private function executeAction(string $action, $route, $data, $fields): TestResponse
    {
        $response = $this->json($action, $route, $data);
        $this->assertInvalidationErros($response, $fields);
/*        $model = $this->model();
        $table = (new $model)->getTable();
        $this->assertDatabaseHas($table, $data);*/
        return $response;
    }

    protected function assertInvalidationErros(TestResponse $response, array $fields)
    {
        foreach ($fields as $field) {
            if (!$field instanceof InvalidationTypes) {
                throw new \Exception('Invalid value from field, must be InvalidationTypes');
            }
            $response
                ->assertStatus($field->getStatus())
                ->assertJsonValidationErrors([str_replace(' ', '_', $field->getField())]);

            $response->assertJsonFragment(
                [\Lang::get("validation.{$field->getValidation()}",
                    ['attribute' => $field->getField()] + $field->getRules())]
            );
        }
    }
}
