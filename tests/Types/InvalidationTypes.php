<?php


namespace Tests\Types;


class InvalidationTypes
{

    private $field;
    private $validation;
    private $rules;
    private $status;

    /**
     * InvalidationTypes constructor.
     * @param $field
     * @param $validation
     * @param $rules
     * @param $status
     */
    public function __construct(string $field, string $validation, array $rules = [], int $status = 422)
    {
        $this->field = str_replace('_', ' ', $field);
        $this->validation = $validation;
        $this->rules = $rules;
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return mixed
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * @return mixed
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

}
