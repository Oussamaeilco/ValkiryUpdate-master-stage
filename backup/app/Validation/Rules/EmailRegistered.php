<?php

namespace App\Validation\Rules;

use App\Models\Employee;
use Respect\Validation\Rules\AbstractRule;
use Slim\Container;

class EmailRegistered extends AbstractRule
{
    /**
     * @var Employee
     */
    protected $employee;

    /**
     * EmailRegistered constructor.
     * @param Employee $employee
     */
    public function __construct(Container $container)
    {
        $this->employee = new Employee($container, [], false);
    }

    /**
     * @param $input
     * @return bool
     */
    public function validate($input)
    {
        return $this->employee->exists(['email' => $input]);
    }
}
