<?php


namespace App\Models;

use Slim\Container;

class QuestionPoolCollection extends Model
{
  /**
   * @var string
   */
  protected $table = 'question_pools';

  /** @var Pool[] $pools */
  private $pools = [];

  /**
   * QuestionPoolCollection constructor.
   * @param Container $container
   * @param array|string|int|null $selector
   */
  public function __construct(Container $container, $selector = null)
  {
      parent::__construct($container);

      $pools = [];

      if ($selector == 'all' || $selector == '*') {
          $pools = $this->selectAll();
      } elseif (is_string($selector) || is_numeric($selector)) {
          $pools = $this->selectAll(['owner_id' => $selector]);
      } elseif (is_array($selector)) {
          $pools = $this->selectAll($selector);
      }

      foreach ($pools as $pool) {
          $this->pools[] = new QuestionPool($container, $pool, 'fetch');
      }
  }

  /**
   * @return array
   */
  public function toArray()
  {
      $pools = [];

      foreach ($this->pools as $pool) {
          $pools[] = $pool->toArray();
      }

      return $pools;
  }


}
