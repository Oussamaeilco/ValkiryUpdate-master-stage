<?php


namespace App\Models;

use Slim\Container;

class PromotionCollection extends Model
{
    /**
     * @var string
     */
    protected $table = 'promotion';

    /** @var Promotion[] $promotions */
    private $promotions = [];

    /**
     * AnswerCollection constructor.
     * @param Container $container
     * @param array|string|int|null $selector
     */
    public function __construct(Container $container, $selector = null)
    {
        parent::__construct($container);


        if ($selector == 'all' || $selector == '*') {
            $promotions = $this->selectAll();
        } elseif (is_string($selector) || is_numeric($selector)) {
            $promotions = $this->selectAll(['owner_id' => $selector]);
        } elseif (is_array($selector)) {
            $promotions = $this->selectAll($selector);
        }

        foreach ($promotions as $promotion) {
            $this->promotions[] = new Promotion($container, $promotion, false);
        }
    }

    /**
     * @param Promotion $promotion
     * @return PromotionCollection
     */
    public function push(Promotion $promotion)
    {
        $this->promotions[] = $promotion;

        return $this;
    }

    /**
     * @return array
     */
    public function indexize()
    {
        $promotions = [];

        foreach ($this->promotions as $promotion) {
            if ($promotion->getId() == 0) {
                continue;
            }
            $promotions[$promotion->getId()] = $promotion->toArray();
        }

        return $promotions;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $promotions = [];

        foreach ($this->promotions as $promotion) {
            $promotions[] = $promotion;
        }

        return $promotions;
    }
}
