<?php

namespace common\modules\tracking\events;

use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class EmailAddedEvent extends BaseObject implements AmplitudeEventInterface
{
    public string $email;

    private User $user;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getInternalName(): string
    {
        return EventEnum::EMAIL_ADDED;
    }

    public function toAmplitude(): array
    {
        return [
            'event_type'       => EventEnum::EMAIL_ADDED,
            'event_properties' => [
                'email' => $this->email,
            ],
        ];
    }
}
