<?php


namespace App\Models;

class User extends Model
{
    /**
     * @var string
     */
    protected $table = 'users';

    /** @var mixed */
    private $id;
    /** @var mixed */
    private $email;
    /** @var mixed */
    private $password;
    /** @var mixed */
    private $account_type;
    /** @var mixed */
    private $session;

    /**
     * @var bool
     */
    private $connected;

    /**
     * @param array $user
     */
    private function setAll($user)
    {
        if (isset($user['id'])) {
            $this->id = $user['id'];
        }
        if (isset($user['email'])) {
            $this->email = $user['email'];
        }
        if (isset($user['password'])) {
            $this->password = $user['password'];
        }
        if (isset($user['account_type'])) {
            $this->account_type = $user['account_type'];
        }
        if (isset($user['session'])) {
            $this->session = $user['session'];
        }
    }

    public function isEmployee(){
        if($this->account_type==0){
            return true;
        }
        return false;
    }

    private function setAllNull()
    {
        $this->id = null;
        $this->email = null;
        $this->password = null;
        $this->account_type = null;
        $this->session = null;
    }

    /**
     * @param bool $remember
     * @return bool
     */
    private function setSession($remember = false)
    {
        $session = uniqid();

        $_SESSION['userSession'] = $session;
        if ($remember) {
            setcookie('userSession', $session, time() + 3600 * 24 * 30, '/');
        }

        if ($this->update(['session' => md5($session)], ['id' => $this->id])) {
            $this->session = md5($session);
            return true;
        }

        return false;
    }

    /**
     * @return mixed|null
     */
    public function getOwnerId()
    {
        if ($this->account_type != 0) {
            return null;
        }

        $statement = "SELECT * FROM employees WHERE email=:email";

        $owner = $this->prepare($statement, ['email' => $this->email]);
        if ($owner) {
            return $owner[0]['owner_id'];
        }

        return null;
    }

    public function getID(){
        return $this->id;
    }
    /**
     * @return mixed|null
     */
    public function getPromotionId()
    {
        if ($this->account_type != 0) {
            return null;
        }

        $statement = "SELECT * FROM employees WHERE email=:email";

        $promotion = $this->prepare($statement, ['email' => $this->email]);
        if ($promotion) {
            return $promotion[0]['promotion_id'];
        }

        return null;
    }

    /**
     * @param array $credentials
     * @param bool $remember
     * @return bool
     */
    public function connect($credentials, $remember = false)
    {
        if (isset($credentials['session'])) {
            return $this->connectSession($credentials['session']);
        }

        $email = $credentials['email'];
        $password = $credentials['password'];

        $user = $this->selectAll(['email' => $email, 'password' => $password]);

        if (empty($user)) {
            return false;
        }

        $this->setAll($user[0]);
        $this->connected = true;

        return $this->setSession($remember);
    }

    /**
     * @param mixed|null $session
     * @return bool
     */
    public function connectSession($session = null)
    {
        $user = $this->selectAll(['session' => md5($session)]);

        if (empty($user)) {
            return false;
        }

        $this->setAll($user[0]);
        $this->connected = true;

        return true;
    }

    /**
     * @param mixed $email
     * @param mixed $password
     * @param mixed $account_type
     * @return bool
     */
    public function register($email, $password, $account_type)
    {
        if ($this->insert(['email' => $email, 'password' => $password, 'account_type' => $account_type])) {
            return $this->connect(['email' => $email, 'password' => $password]);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function disconnect()
    {
        unset($_SESSION['userSession']);
        unset($_COOKIE['userSession']);
        setcookie('userSession', null, -1, '/');
        $this->setAllNull();

        return true;
    }

    /**
     * @param array $credentials
     * @return bool
     */
    public function exists($credentials)
    {
        return boolval($this->count($credentials));
    }

    /**
     * @param mixed $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if ($name != 'table') {
            return $this->$name;
        }

        return null;
    }

    public function changeEmail($email){
      return $this->update(['email' => $email], ['id' => $this->id]);
    }

    public function changePassword($oldPassword, $password, $email){
      if($this->connect(['email' => $email, 'password' => $oldPassword])){
        return $this->update(['password' => $password], ['id' => $this->id]);
      }
      else{
        return false;
      }
    }

    
}
