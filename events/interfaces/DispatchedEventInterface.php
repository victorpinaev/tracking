<?php

namespace common\modules\tracking\events\interfaces;

use domain\user\models\User;

interface DispatchedEventInterface
{
    /**
     * @return User
     */
    public function getUser(): User;

    /**
     * @return string
     */
    public function getInternalName(): string;
}
