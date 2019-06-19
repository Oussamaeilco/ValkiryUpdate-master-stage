<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class EmailRegisteredException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Your email is not related to a company with a license'
        ],
    ];
}
