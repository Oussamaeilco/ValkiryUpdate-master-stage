<?php


namespace App\Models;

use Slim\Container;

class Answer extends Model
{
    /**
     * @var string
     */
    protected $table = 'answers';

    /** @var mixed */
    private $id;
    /** @var mixed */
    private $question_id;
    /** @var mixed */
    private $answer;

    /**
     * Answer constructor.
     * @param Container $container
     * @param array $array
     * @param bool $fetch
     */
    public function __construct(Container $container, $array = [], $fetch = true)
    {
        parent::__construct($container);

        if ($fetch) {
            $question = $this->selectAll($array);

            if (!empty($question)) {
                $this->setAll($question[0]);
            }
        } else {
            $this->setAll($array);
        }
    }

    /**
     * @param array $answer
     */
    private function setAll($answer = [])
    {
        if (isset($answer['id'])) {
            $this->id = $answer['id'];
        }
        if (isset($answer['question_id'])) {
            $this->question_id = $answer['question_id'];
        }
        if (isset($answer['answer'])) {
            $this->answer = $answer['answer'];
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
     * @return int
     */
    public function getQuestionId()
    {
        return intval($this->question_id);
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
    public function add($exists = ['question_id'])
    {
        if ($this->exists($exists)) {
            return false;
        }

        if (isset($exists['question_id'])) {
            return boolval($this->insert(array_replace(array_slice($this->toArray(), 1), ['question_id' => $exists['question_id']])));
        }

        return boolval($this->insert(array_slice($this->toArray(), 1)));
    }

    public function modifyAnswer(){
      return boolval($this->update(array_slice($this->toArray(), 2), ['question_id' => $this->question_id]));
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'question_id' => $this->question_id,
            'answer' => $this->answer
        ];
    }
}
