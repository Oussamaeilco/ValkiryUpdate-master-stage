<?php


namespace App\Models;

use Slim\Container;

class EmployeeCollection extends Model
{
    /**
     * @var string
     */
    protected $table = 'employees';

    /** @var Employee[] $employees */
    private $employees = [];

    /**
     * EmployeeCollection constructor.
     * @param Container $container
     * @param array|string|int|null $selector
     */
    public function __construct(Container $container, $selector = null)
    {
        parent::__construct($container);

        $employees = [];

        if ($selector == 'all' || $selector == '*') {
            $employees = $this->selectAll();
        } elseif (is_numeric($selector) || is_string($selector)) {
            $employees = $this->selectAll(['owner_id' => $selector]);
        } elseif (is_array($selector)) {
            $employees = $this->selectAll($selector);
        }

        foreach ($employees as $employee) {
            $this->employees[] = new Employee($container, $employee, false);
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $employees = [];

        foreach ($this->employees as $employee) {
            $employees[] = $employee->toArray();
        }

        return $employees;
    }
}
