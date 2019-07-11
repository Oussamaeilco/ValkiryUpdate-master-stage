<?php


namespace App\Models;

use Slim\Container;

class QuestionPool extends Model
{
    /**
     * @var string
     */
    protected $table = 'question_pools';

    /**
     * @var Container
     */
    protected $container;

    /** @var mixed */
    private $id;
    /** @var mixed */
    private $owner_id;
    /** @var mixed */
    private $period_start;
    /** @var mixed */
    private $period_end;
    /** @var mixed */
    private $reponse;
    /**
     * @var QuestionCollection
     */
    private $collection;

    /** @var string $FETCH */
    public static $FETCH = 'fetch';
    /** @var string $CREATE */
    public static $CREATE = 'create';
    /** @var string $FETCH_CREATE */
    public static $FETCH_CREATE = 'fetch_create';

    /**
     * QuestionPool constructor.
     * @param Container $container
     * @param array $array
     * @param string|null $mode
     * @param bool $autofill
     */
    public function __construct(Container $container, $array, $mode = null, $autofill = true, $promotion_id = null)
    {
        parent::__construct($container);

        switch ($mode) {
            case self::$FETCH:
                $this->fetch($array);
                break;
            case self::$CREATE:
                $this->setAll($array, $autofill);
                $this->add();
                break;
            case 'create_manual':
                $this->setAll($array, $autofill);
                break;
            case self::$FETCH_CREATE:
            default:
                $this->fetchCreate($array, $autofill);
        }

        if (!is_null($this->id) && $promotion_id!=null) {
            $this->collection = new QuestionCollection($container, ['pool_id' => $this->id, 'promotion_id' => $promotion_id]);
        }
        else if(!is_null($this->id)){
            $this->collection = new QuestionCollection($container, ['pool_id' => $this->id]);
        }
    }

    /**
     * @param $pool
     * @param bool $autofill
     */
    private function setAll($pool, $autofill = false)
    {
        if (isset($pool['id'])) {
            $this->id = $pool['id'];
        }
        if (isset($pool['owner_id'])) {
            $this->owner_id = $pool['owner_id'];
        }

        if (isset($pool['period_start'])) {
            $this->period_start = $pool['period_start'];
        } elseif ($autofill) {
            $this->period_start = date('Y-m-d', strtotime('today'));
        }

        if (isset($pool['period_end'])) {
            $this->period_end = $pool['period_end'];
        } elseif ($autofill) {
            $this->period_end = date('Y-m-d', strtotime('+6 days'));
        }

        if(isset($pool['reponse'])){
            $this->reponse=$pool['reponse'];
        } elseif ($autofill) {
            $this->reponse= date('Y-m-d', strtotime('+14 days'));
        }
    }

    /**
     * @param array $array
     */
    public function fetch($array = [])
    {
        $pool = $this->selectAll($array);

        if (!empty($pool)) {
            $this->setAll($pool[0]);
        }
    }

    /**
     * @param array $array
     * @param bool $autofill
     */
    public function fetchCreate($array = [], $autofill = true)
    {
        $active = false;
        $pools = $this->selectAll($array);

        foreach ($pools as $pool) {
            $this->setAll($pool);

            if ($this->isActive()) {
                $active = true;
                break;
            }
        }

        if (!$active) {
            $this->setAll($array, $autofill);
            $this->add();
        }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array $conditions
     * @param bool $autofill
     * @return QuestionCollection
     */
    public function getQuestions($conditions = [], $autofill = true)
    {
        if (empty($conditions)) {
            return $this->collection;
        }

        if (!isset($conditions['pool_id']) && $autofill) {
            $conditions['pool_id'] = $this->id;
        }

        return new QuestionCollection($this->container, $conditions);
    }
    /*
    public function getExpiration($timeout = 8)
    {
        $period_end = strtotime($this->period_end);
        $expired = strtotime("+ {$timeout} days", $period_end);

        return date('Y-m-d', $expired);
    }
    */

    public function getExpiration()
    {
        $rep = strtotime($this->reponse);
        
        return date('Y-m-d', $rep);
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasAsked($id)
    {
        return boolval($this->prepare('SELECT COUNT(*) FROM questions WHERE employee_id=:id AND pool_id=:pool_id', ['id' => $id, 'pool_id' => $this->id])[0]['COUNT(*)']);
    }

    /**
     * @param mixed $user_id
     * @return QuestionCollection
     */
    public function getVoted($user_id)
    {
        $questions = new QuestionCollection($this->container);
        $statement = "SELECT COUNT(*) FROM votes WHERE question_id=:question_id AND employee_id=:employee_id";

        foreach ($this->collection->getQuestions() as $question) {
            if (boolval($this->prepare($statement, ['question_id' => $question->getId(), 'employee_id' => $user_id])[0]['COUNT(*)'])) {
                $questions->push($question);
            }
        }

        return $questions;
    }

    /**
     * @param int $limit
     * @return QuestionCollection
     */
    public function upvoted($limit = 3)
    {
        $questions = new QuestionCollection($this->container, ['pool_id' => $this->id]);

        return $questions->upvoted($limit);
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

    public function idFor($conditions)
    {
        foreach ($conditions as $index => $condition) {
            if (isset($this->$condition)) {
                unset($conditions[$index]);
                $conditions[$condition] = $this->$condition;
            }
        }

        $pool = $this->select(['id'], $conditions, 1);

        if (empty($license)) {
            return null;
        }

        return $pool[0]['id'];
    }
    /**
     * @param int $limit
     * @return QuestionCollection
     */
    /* Fonction triant les questions en fonction de leur poids lié aux votes
    public function random($limit = 3)
    {
        $questions = $this->collection->getQuestions();
        $count = count($questions);*/
        /** @var Question[] $weighted */
        /*$weighted = [];
        $ids = [];
        $collection = new QuestionCollection($this->container);

        foreach ($questions as $question) {
            if ($count <= $limit) {
                $collection->push($question);
            } else {
                for ($i = 0; $i < $question->getWeight($count); $i++) {
                    $weighted[] = $question;
                }
            }
        }

        if ($count <= $limit) {
            return $collection;
        }

        for ($i = 0; $i < $limit; $i++) {
            $index = rand(0, $count - 1);
            $id = $weighted[$index]->getId();

            while (in_array($id, $ids)) {
                $index = rand(0, count($weighted) - 1);
                $id = $weighted[$index]->getId();
            }

            $ids[] = $id;
            $collection->push($weighted[$index]);
        }

        return $collection;
    }*/

    /**
     * @param int $limit
     * @return QuestionCollection
     */
    public function random($limit = 3)
    {
        $questions = $this->collection->getQuestions();
        $count = count($questions);
        $ids = [];
        $collection = new QuestionCollection($this->container);

        foreach ($questions as $question) {
            if ($count <= $limit) {
                $collection->push($question);
            }
        }

        if ($count <= $limit) {
            return $collection;
        }

        for ($i = 0; $i < $limit; $i++) {
            $index = rand(0, $count - 1);
            $id = $questions[$index]->getId();

            while (in_array($id, $ids)) {
                $index = rand(0, count($questions) - 1);
                $id = $questions[$index]->getId();
            }

            $ids[] = $id;
            $collection->push($questions[$index]);
        }

        return $collection;
    }

    /**
     * @param int $timeout
     * @return bool
     */
    public function isActive($timeout = 3)
    {
        $period_start = strtotime($this->period_start);
        $period_end = strtotime($this->period_end);
        $expired = strtotime("+ {$timeout} days", $period_end);
        $today = strtotime('today');

        return ($expired >= $today && $period_start <= $today);
    }
    public function getValues(){
        return "==>".$this->id ."=".$this->period_start."=".$this->period_end."=".$this->reponse;
    }

    /**
     * @param array|null $array
     * @param array|null $conditions
     * @return bool
     */
    public function edit($array = null, $conditions = null)
    {
        if ($array) {
            return $this->update($array, $conditions);
        }

        return boolval($this->update(array_slice($this->toArray(), 1), ['id' => $this->id]));
    }

    /**
     * @return bool
     */
    public function isOpen()
    {
        return $this->isActive(0);
    }

    /**
     * @param Question $question
     * @return bool
     */
    public function addQuestion(Question $question)
    {
        return $question->add(['pool_id' => $this->id, 'employee_id']);
    }

    /**
     * @return bool
     */
    public function add()
    {
        $array = array_slice($this->toArray(), 1);

        if ($this->insert($array)) {
            $this->fetch($array);

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function remove()
    {
        return boolval($this->delete(['id' => $this->id]));
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'period_start' => $this->period_start,
            'period_end' => $this->period_end,
            'reponse' => $this->reponse
        ];
    }
}
