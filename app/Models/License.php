<?php


namespace App\Models;

use Slim\Container;

class License extends Model
{
    /**
     * @var string
     */
    protected $table = 'licenses';

    /** @var mixed */
    private $id;
    /** @var mixed */
    private $user_email;
    /** @var mixed */
    private $license;
    /** @var mixed */
    private $start_date;
    /** @var mixed */
    private $end_date;

    /** @var string $STATUS_EMPTY */
    public static $STATUS_EMPTY = 'empty';
    /** @var string $STATUS_EXPIRED */
    public static $STATUS_EXPIRED = 'expired';
    /** @var string $STATUS_UNSTARTED */
    public static $STATUS_UNSTARTED = 'unstarted';
    /** @var string $STATUS_ACTIVE */
    public static $STATUS_ACTIVE = 'active';

    /**
     * License constructor.
     * @param Container $container
     * @param array $array
     * @param bool $fetch
     * @param bool $autofill
     */
    public function __construct(Container $container, $array = [], $fetch = true, $autofill = true)
    {
        parent::__construct($container);

        if ($fetch) {
            $license = $this->selectAll($array);

            if (!empty($license)) {
                $this->setAll($license[0]);
            }
        } else {
            $this->setAll($array, $autofill);
        }
    }

    /**
     * @param array $license
     * @param bool $autofill
     */
    private function setAll($license = [], $autofill = false)
    {
        if (isset($license['id'])) {
            $this->id = $license['id'];
        }
        if (isset($license['user_email'])) {
            $this->user_email = $license['user_email'];
        }

        if (isset($license['license'])) {
            $this->license = $license['license'];
        } elseif ($autofill) {
            $this->license = $this->generateKey();
        }

        if (isset($license['start_date'])) {
            $this->start_date = $license['start_date'];
        } elseif ($autofill) {
            $this->start_date = date('Y-m-d', strtotime('today'));
        }

        if (isset($license['end_date'])) {
            $this->end_date = $license['end_date'];
        } elseif ($autofill) {
            $this->start_date = date('Y-m-d', strtotime('+1 Year'));
        }
    }

    /**
     * @param int $min
     * @param int $max
     * @param int $occurrences
     * @return string
     */
    private function generateKey($min = 1000, $max = 9999, $occurrences = 5)
    {
        $key = implode('-', str_split(substr(strtoupper(md5(time() . uniqid(rand($min, $max)))), 0, 20), $occurrences));

        if ($this->exists(['license' => $key])) {
            $key = $this->generateKey($min, $max, $occurrences);
        }

        return $key;
    }

    /**
     * @param string $user_email
     * @return bool
     */
    public function apply($user_email)
    {
        return boolval($this->update(['user_email' => $user_email], ['id' => $this->id]));
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->license;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        if ($this->id == null || $this->license == null) {
            return self::$STATUS_EMPTY;
        }

        $start_date = strtotime($this->start_date);
        $end_date = strtotime($this->end_date);
        $today = strtotime('today');

        if ($start_date <= $today && $end_date < $today) {
            return self::$STATUS_EXPIRED;
        }
        if ($start_date > $today && $end_date >= $today) {
            return self::$STATUS_UNSTARTED;
        }

        return self::$STATUS_ACTIVE;
    }

    /**
     * @param array $conditions
     * @return string|number|null
     */
    public function idFor($conditions)
    {
        foreach ($conditions as $index => $condition) {
            if (isset($this->$condition)) {
                unset($conditions[$index]);
                $conditions[$condition] = $this->$condition;
            }
        }

        $license = $this->select(['id'], $conditions, 1);

        if (empty($license)) {
            return null;
        }

        return $license[0]['id'];
    }

    /**
     * @return bool
     */
    public function add()
    {
        return boolval($this->insert(array_slice($this->toArray(), 1)));
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
    public function abort()
    {
        return boolval($this->delete(['id' => $this->id]));
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
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'user_email' => $this->user_email,
            'license' => $this->license,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date
        ];
    }
}
