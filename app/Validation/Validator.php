<?php

namespace App\Validation;

use Respect\Validation\Validator as Respect;
use Respect\Validation\Exceptions\NestedValidationException;
use Slim\Http\Request;
use App\Controllers\Controller;

class Validator
{
    protected $errors;

    /**
     * Check for rules validation
     * @param Request $request
     * @param array $rules
     * @return $this
     */
    public function validate(Request $request, array $rules)
    {
        foreach ($rules as $field => $rule) {
            try {
                $rule->setName($field)->assert($request->getParam($field));
            } catch (NestedValidationException $e) {
                $this->errors[$field] = str_replace('input', '', $e->getMessages());
            }
        }

        Controller::flash($this->errors, 'formErrors');

        return $this;
    }

    /**
     * @return bool
     */
    public function failed()
    {
        return !empty($this->errors);
    }
}
