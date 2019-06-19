<?php


namespace App\Models;

use Slim\Container;

class LicenseCollection extends Model
{
    /**
     * @var string
     */
    protected $table = 'licenses';

    /** @var License[] $licenses */
    private $licenses = [];

    /**
     * LicenseCollection constructor.
     * @param Container $container
     * @param array|string|null $selector
     */
    public function __construct(Container $container, $selector = null)
    {
        parent::__construct($container);

        $licenses = [];

        if ($selector == 'all' || $selector == '*') {
            $licenses = $this->selectAll();
        } elseif ($selector == 'active') {
            $licenses = $this->fetchActive();
        } elseif ($selector == 'expired') {
            $licenses = $this->fetchExpired();
        } elseif ($selector == 'unstarted') {
            $licenses = $this->fetchUnstarted();
        } elseif (is_string($selector)) {
            $licenses = $this->selectAll(['user_email' => $selector]);
        } elseif (is_array($selector)) {
            $licenses = $this->selectAll($selector);
        }

        foreach ($licenses as $license) {
            $this->licenses[] = new License($container, $license, false);
        }
    }

    /**
     * @return array
     */
    private function fetchActive()
    {
        return $this->query("SELECT * FROM {$this->table} WHERE start_date <= now() AND end_date >= now()");
    }

    /**
     * @return array
     */
    private function fetchExpired()
    {
        return $this->query("SELECT * FROM {$this->table} WHERE start_date <= now() AND end_date < now()");
    }

    /**
     * @return array
     */
    private function fetchUnstarted()
    {
        return $this->query("SELECT * FROM {$this->table} WHERE start_date > now() AND end_date >= now()");
    }

    /**
     * @return array
     */
    public function categorize()
    {
        $licenses = [];

        $licenses[License::$STATUS_ACTIVE] = [];
        $licenses[License::$STATUS_EXPIRED] = [];
        $licenses[License::$STATUS_UNSTARTED] = [];

        foreach ($this->licenses as $license) {
            $licenses[$license->getStatus()][] = $license->toArray();
        }

        return $licenses;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $licenses = [];

        foreach ($this->licenses as $license) {
            $licenses[] = $license->toArray();
        }

        return $licenses;
    }
}
