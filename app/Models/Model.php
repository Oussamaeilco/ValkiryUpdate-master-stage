<?php

namespace App\Models;

use Slim\Container;

class Model
{

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var String
     */
    protected $table;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->pdo = $container->get('pdo');
    }

    //////////////////////////////
    //
    //     Static builders
    //
    //////////////////////////////

    /**
     * Build attributes part of SQL statement
     * @param array $attributes
     * @return string
     */
    public static function buildAttributes($attributes = [])
    {
        $statement = "";

        foreach ($attributes as $field => $value) {
            $statement .= "{$field}=:{$field}, ";
        }

        $statement = rtrim($statement, ', ');

        return $statement;
    }

    /**
     * Build conditions part of SQL statement
     * @param array $conditions
     * @return string
     */
    public static function buildConditions($conditions = [])
    {
        $statement = "";

        if (empty($conditions)) {
            $statement .= "1=1";
        } else {
            foreach ($conditions as $field => $value) {
                if (is_null($value)) {
                    $statement .= "{$field} IS NULL AND ";
                } else {
                    $statement .= "{$field}=:{$field} AND ";
                }
            }

            $statement = rtrim($statement, ' AND ');
        }

        return $statement;
    }

    /**
     * Build limit part of SQL statement
     * @param mixed $limit
     * @param mixed $offset
     * @return string
     */
    public static function buildLimit($limit = null, $offset = null)
    {
        $statement = "";

        if ($limit != null && $offset != null) {
            if (is_numeric($limit)) {
                $statement .= " LIMIT {$limit}";
            }

            if (is_numeric($offset)) {
                $statement .= " OFFSET {$offset}";
            }
        }

        return $statement;
    }

    //////////////////////////////
    //
    //     Protected methods
    //
    //////////////////////////////

    /**
     * PDO Query
     * @param $statement
     * @return array
     */
    protected function query($statement)
    {
        $request = $this->pdo->query($statement);

        return $request->fetchAll();
    }

    /**
     * PDO Prepared request
     * @param string $statement
     * @param array $attributes
     * @param bool $fetch
     * @return mixed
     */
    protected function prepare($statement, $attributes = [], $fetch = true)
    {
        $request = $this->pdo->prepare($statement);
        $request->execute($attributes);

        if ($fetch) {
            return $request->fetchAll();
        }

        return $request;
    }

    /**
     * @param array $conditions
     * @return mixed
     */
    protected function count($conditions = [])
    {
        $statement = "SELECT COUNT(*) FROM {$this->table} WHERE ";
        $statement .= self::buildConditions($conditions);

        return $this->prepare($statement, $conditions)[0]['COUNT(*)'];
    }

    /**
     * @param array $conditions
     * @param int|string|null $limit
     * @param int|string|null $offset
     * @return mixed
     */
    protected function selectAll($conditions = [], $limit = null, $offset = null)
    {
        $statement = "SELECT * FROM {$this->table} WHERE ";
        $statement .= self::buildConditions($conditions);
        $statement .= self::buildLimit($limit, $offset);

        return $this->prepare($statement, $conditions);
    }

    /**
     * @param array $fields
     * @param array $conditions
     * @param int|string|null $limit
     * @param int|string|null $offset
     * @return mixed
     */
    protected function select($fields = [], $conditions = [], $limit = null, $offset = null)
    {
        if (empty($fields) || $fields == "*") {
            return $this->selectAll($conditions, $limit, $offset);
        } else {
            $statement = "SELECT ";

            foreach ($fields as $field) {
                $statement .= "{$field}, ";
            }

            $statement = rtrim($statement, ', ');
        }

        $statement .= " FROM {$this->table} WHERE ";
        $statement .= self::buildConditions($conditions);
        $statement .= self::buildLimit($limit, $offset);

        return $this->prepare($statement, $conditions);
    }

    /**
     * @param array $attributes
     * @return mixed
     */
    protected function insert($attributes)
    {
        if (empty($attributes)) {
            return false;
        }

        $statement = "INSERT INTO {$this->table} SET ";
        $statement .= self::buildAttributes($attributes);

        return $this->pdo->prepare($statement)->execute($attributes);
    }

    /**
     * @param array $attributes
     * @param array $conditions
     * @return mixed
     */
    protected function update($attributes, $conditions)
    {
        if (empty($attributes)) {
            return false;
        }

        $statement = "UPDATE {$this->table} SET ";
        $statement .= self::buildAttributes($attributes);
        $statement .= " WHERE ";
        $statement .= self::buildConditions($conditions);

        return $this->pdo->prepare($statement)->execute(array_merge($attributes, $conditions));
    }

    /**
     * @param array $conditions
     * @return mixed
     */
    protected function delete($conditions)
    {
        $statement = "DELETE FROM {$this->table} WHERE ";
        $statement .= self::buildConditions($conditions);

        return $this->pdo->prepare($statement)->execute($conditions);
    }
}
