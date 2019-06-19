<?php


namespace App\Models;

use Slim\Container;

class Employee extends Model
{
    /**
     * @var string
     */
    protected $table = 'employees';

    /** @var mixed */
    private $id;
    /** @var mixed */
    private $email;
    /** @var mixed */
    private $owner_id;

    /**
     * Employee constructor.
     * @param Container $container
     * @param array $array
     * @param bool $fetch
     */
    public function __construct(Container $container, $array = [], $fetch = true)
    {
        parent::__construct($container);

        if ($fetch) {
            $employee = $this->selectAll($array);

            if (!empty($employee)) {
                $this->setAll($employee[0]);
            }
        } else {
            $this->setAll($array);
        }
    }

    /**
     * @param array $employee
     */
    private function setAll($employee = [])
    {
        if (isset($employee['id'])) {
            $this->id = $employee['id'];
        }
        if (isset($employee['email'])) {
            $this->email = $employee['email'];
        }
        if (isset($employee['owner_id'])) {
            $this->owner_id = $employee['owner_id'];
        }
    }

    /**
     * @return bool
     */
    public function add()
    {
        if ($this->exists(['email', 'owner_id'])) {
            return false;
        }
        return boolval($this->insert(array_slice($this->toArray(), 1)));
    }

    /**
     * @return bool
     */
    public function remove()
    {
        return boolval($this->delete(['id' => $this->id]));
    }

    /**
     * @param array $conditions
     * @return bool
     */
    public function exists($conditions)
    {
        foreach ($conditions as $key => $condition) {
            if (is_numeric($key) && isset($this->$condition)) {
                unset($conditions[$key]);
                $conditions[$condition] = $this->$condition;
            }
        }

        return boolval($this->count($conditions));
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'owner_id' => $this->owner_id
        ];
    }
}
