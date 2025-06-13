<?php

namespace Core;

use Core\Bases\BaseValidator;
use Respect\Validation\Validator as v;

class Validator extends BaseValidator
{

    public function validate()
    {
        $this->validateField('username', v::alnum()->noWhitespace()->length(1, 15));
        $this->validateField('email', v::email());
        $this->validateField('password', v::stringType()->length(8, null));
    }
}