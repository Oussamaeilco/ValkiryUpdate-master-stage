<?php


namespace App\Models;

use Slim\Container;

class Question extends Model
{
    /**
     * @var string
     */
    protected $table = 'questions';

    /** @var mixed */
    private $id;
    /** @var mixed */
    private $employee_id;
    /** @var mixed */
    private $subject;
    /** @var mixed */
    private $question;
    /** @var mixed */
    private $votes;
    /** @var mixed */
    private $pool_id;

    /**
     * Question constructor.
     * @param Container $container
     * @param array $array
     * @param bool $fetch
     * @param bool $autofill
     */
    public function __construct(Container $container, $array = [], $fetch = true, $autofill = true)
    {
        parent::__construct($container);

        if ($fetch) {
            $question = $this->selectAll($array);

            if (!empty($question)) {
                $this->setAll($question[0], $autofill);
            }
        } else {
            $this->setAll($array, $autofill);
        }
    }

    /**
     * @param array $question
     * @param bool $autofill
     */
    private function setAll($question = [], $autofill = false)
    {
        if (isset($question['id'])) {
            $this->id = $question['id'];
        }
        if (isset($question['employee_id'])) {
            $this->employee_id = $question['employee_id'];
        }
        if (isset($question['subject'])) {
            $this->subject = $question['subject'];
        }
        if (isset($question['question'])) {
            $this->question = $question['question'];
        }
        if (isset($question['pool_id'])) {
            $this->pool_id = $question['pool_id'];
        }

        if (isset($question['votes'])) {
            $this->votes = $question['votes'];
        } elseif ($autofill) {
            $this->votes = $this->getVotes();
        }
    }

    /**
     * @return int
     */
    public function getEmployeeID()
    {   
        return $this->employee_id;
    }

    /**
     * @return Answer
     */
    public function getAnswer()
    {
        return new Answer($this->container, ['question_id' => $this->id]);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getVotes()
    {
        $statement = 'SELECT COUNT(*) FROM votes WHERE question_id=:question_id';

        return intval($this->prepare($statement, ['question_id' => $this->id])[0]['COUNT(*)']);
    }

    /**
     * @param int $factor
     * @return int
     */
    public function getWeight($factor = 1)
    {
        return floor(exp(-0.1 * intval($this->votes)) * $factor) + 1;
    }

    /**
     * @param mixed $user_id
     * @return bool
     */
    public function hasVoted($user_id)
    {
        $statement = 'SELECT COUNT(*) FROM votes WHERE employee_id=:employee_id AND question_id=:question_id';

        return boolval($this->prepare($statement, ['employee_id' => $user_id, 'question_id' => $this->id])[0]['COUNT(*)']);
    }

    /**
     * @param mixed $user_id
     * @return bool
     */
    public function vote($user_id)
    {
        $statement = 'SELECT COUNT(*) FROM votes WHERE employee_id=:employee_id AND question_id=:question_id';
        $numberOfUserVotes = $this->prepare($statement, ['employee_id' => $user_id, 'question_id' => $this->id])[0]['COUNT(*)'];
        if ($numberOfUserVotes > 3) {
            return false;
        }

        $statement = 'INSERT INTO votes SET employee_id=:employee_id, question_id=:question_id';

        return boolval($this->prepare($statement, ['employee_id' => $user_id, 'question_id' => $this->id], false));
    }

    /**
     * @param mixed $user_id
     * @return bool
     */
    public function unvote($user_id)
    {
        if (!$this->hasVoted($user_id)) {
            return false;
        }

        $statement = 'DELETE FROM votes WHERE employee_id=:employee_id AND question_id=:question_id';

        return boolval($this->prepare($statement, ['employee_id' => $user_id, 'question_id' => $this->id], false));
    }

    /**
     * @param Answer $answer
     * @return bool
     */
    public function answer(Answer $answer)
    {
        return $answer->add(['question_id' => $this->id]);
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
    public function add($exists = ['pool_id', 'employee_id'])
    {
        $attributes = $this->toArray();
        if (isset($attributes['votes'])) {
            unset($attributes['votes']);
        }

        if ($this->exists($exists)) {
            return false;
        }
        if (isset($exists['pool_id'])) {
            return boolval($this->insert(array_replace(array_slice($attributes, 1), ['pool_id' => $exists['pool_id']])));
        }

        return boolval($this->insert(array_slice($attributes, 1)));
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'subject' => $this->subject,
            'question' => $this->question,
            'votes' => $this->votes,
            'pool_id' => $this->pool_id
        ];
    }
}
