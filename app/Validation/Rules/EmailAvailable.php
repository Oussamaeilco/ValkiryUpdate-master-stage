<?php

namespace App\Validation\Rules;

use App\Models\User;
use Respect\Validation\Rules\AbstractRule;

class EmailAvailable extends AbstractRule
{
    /**
     * @var User
     */
    protected $user;

    /**
     * EmailAvailable constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param $input
     * @return bool
     */
    public function validate($input)
    {
        return !$this->user->exists(['email' => $input]);
    }
}
