<?php


namespace App\Models;

use Slim\Container;

class AnswerCollection extends Model
{
    /**
     * @var string
     */
    protected $table = 'answers';

    /** @var Answer[] $answers */
    private $answers = [];

    /**
     * AnswerCollection constructor.
     * @param Container $container
     * @param array|string|int|null $selector
     */
    public function __construct(Container $container, $selector = null)
    {
        parent::__construct($container);

        $answers = [];

        if ($selector == 'all' || $selector == '*') {
            $answers = $this->selectAll();
        } elseif (is_string($selector) || is_numeric($selector)) {
            $answers = $this->selectAll(['id' => $selector]);
        } elseif (is_array($selector)) {
            $answers = $this->selectAll($selector);
        }

        foreach ($answers as $answer) {
            $this->answers[] = new Answer($container, $answer, false);
        }
    }

    /**
     * @param Answer $answer
     * @return AnswerCollection
     */
    public function push(Answer $answer)
    {
        $this->answers[] = $answer;

        return $this;
    }

    /**
     * @return array
     */
    public function indexize()
    {
        $answers = [];

        foreach ($this->answers as $answer) {
            if ($answer->getId() == 0) {
                continue;
            }
            $answers[$answer->getQuestionId()] = $answer->toArray();
        }

        return $answers;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $answers = [];

        foreach ($this->answers as $answer) {
            $answers[] = $answer->toArray();
        }

        return $answers;
    }
}
