<?php

namespace Core\Bases;

use Respect\Validation\Exceptions\NestedValidationException;

abstract class BaseValidator
{
    protected $data;
    protected $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    abstract public function validate();

    public function getErrors()
    {
        return $this->errors;
    }

    protected function validateField($field, $validator)
    {
        try {
            $validator->setName($field)->assert($this->data[$field]);
        } catch (NestedValidationException $exception) {
            $this->errors[$field] = $exception->getMessages();
        }
    }
}