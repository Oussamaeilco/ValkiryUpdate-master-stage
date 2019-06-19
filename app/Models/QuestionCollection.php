<?php


namespace App\Models;

use Slim\Container;

class QuestionCollection extends Model
{
    /**
     * @var string
     */
    protected $table = 'questions';

    /** @var Question[] $questions */
    private $questions = [];

    /**
     * QuestionCollection constructor.
     * @param Container $container
     * @param array|string|int|null $selector
     */
    public function __construct(Container $container, $selector = null)
    {
        parent::__construct($container);

        $questions = [];

        if ($selector == 'all' || $selector == '*') {
            $questions = $this->selectAll();
        } elseif (is_string($selector) || is_numeric($selector)) {
            $questions = $this->selectAll(['pool_id' => $selector]);
        } elseif (is_array($selector)) {
            $questions = $this->selectAll($selector);
        }

        foreach ($questions as $question) {
            $this->questions[] = new Question($container, $question, false);
        }
    }

    /**
     * @return Question[]
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * @return AnswerCollection
     */
    public function getAnswers()
    {
        $answers = new AnswerCollection($this->container);

        foreach ($this->questions as $question) {
            $answers->push(new Answer($this->container, ['question_id' => $question->getId()]));
        }

        return $answers;
    }

    /**
     * @param int $limit
     * @return QuestionCollection
     */
    public function upvoted($limit = 3)
    {
        $votes = [];

        foreach ($this->questions as $key => $question) {
            $votes[$key] = $question->getVotes();
        }

        array_multisort($votes, SORT_DESC, $this->questions);
        $this->questions = array_slice($this->questions, 0, $limit);

        return $this;
    }

    /**
     * @param Question $question
     * @return QuestionCollection
     */
    public function push(Question $question)
    {
        $this->questions[] = $question;

        return $this;
    }

    /**
     * @param QuestionCollection $collection
     * @return QuestionCollection
     */
    public function merge(QuestionCollection $collection)
    {
        foreach ($collection->getQuestions() as $otherQuestion) {
            $this->questions[] = $otherQuestion;
        }

        return $this;
    }

    /**
     * @param QuestionCollection $collection
     * @param array $fields
     * @return QuestionCollection
     */
    public function except(QuestionCollection $collection, $fields = ['id'])
    {
        foreach ($collection->toArray() as $exceptQuestion) {
            foreach ($this->questions as $index => $question) {
                foreach ($fields as $field) {
                    if (isset($question->toArray()[$field]) && isset($exceptQuestion[$field])) {
                        if ($question->toArray()[$field] == $exceptQuestion[$field]) {
                            unset($this->questions[$index]);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @return QuestionCollection
     */
    public function uniq()
    {
        for ($i = 0; $i < count($this->questions); $i++) {
            for ($j = $i + 1; $j < count($this->questions); $j++) {
                if ($this->questions[$i]->getId() == $this->questions[$j]->getId()) {
                    unset($this->questions[$i]);
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * @param int $limit
     * @return QuestionCollection
     */
    public function restrict($limit = 3)
    {
        shuffle($this->questions);
        $this->questions = array_slice($this->questions, 0, $limit);

        return $this;
    }

    /**
     * @return array
     */
    public function indexize()
    {
        $questions = [];

        foreach ($this->questions as $question) {
            if ($question->getId() == 0) {
                continue;
            }
            $questions[$question->getId()] = $question->toArray();
        }

        return $questions;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $questions = [];

        foreach ($this->questions as $question) {
            $questions[] = $question->toArray();
        }

        return $questions;
    }
}
