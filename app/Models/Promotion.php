<?php

namespace App\Models;

use Slim\Container;

class Promotion extends Model
{
    /**
     * @var string
     */
    protected $table = 'promotion';

    /** @var mixed */
    private $id;
    /** @var mixed */
    private $description;
    /** @var mixed */
    private $owner_id;

    /**
     * Promotion constructor.
     * @param Container $container
     * @param array $array
     * @param bool $fetch
     */
    public function __construct(Container $container, $array = [], $fetch = true)
    {
        parent::__construct($container);

        if ($fetch) {
            $promotion = $this->selectAll($array);

            if (!empty($promotion)) {
                $this->setAll($promotion[0]);
            }
        } else {
            $this->setAll($array);
        }
    }

    /**
     * @param array $promotion
     */
    private function setAll($promotion = [])
    {
        if (isset($promotion['id'])) {
            $this->id = $promotion['id'];
        }
        if (isset($promotion['description'])) {
            $this->description = $promotion['description'];
        }
        if (isset($promotion['owner_id'])) {
            $this->owner_id = $promotion['owner_id'];
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return intval($this->id);
    }

     /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * @return int
     */
    public function getOwnerId()
    {
        return intval($this->owner_id);
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
     * @param array $exists
     * @return bool
     */
    public function add($exists = ['description'])
    {
        if ($this->exists($exists)) {
            return false;
        }

        if (isset($exists['description'])) {
            return boolval($this->insert(array_replace(array_slice($this->toArray(), 1), ['description' => $exists['description']])));
        }

        return boolval($this->insert(array_slice($this->toArray(), 1)));
    }

    public function modifyPromotion(){
      return boolval($this->update(array_slice($this->toArray(), 2), ['id' => $this->id]));
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'owner_id' =>$this->owner_id
        ];
    }
}
