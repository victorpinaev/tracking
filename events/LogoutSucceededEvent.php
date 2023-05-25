<?php

namespace common\modules\tracking\events;

use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class LogoutSucceededEvent extends BaseObject implements AmplitudeEventInterface
{
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
        return EventEnum::LOGOUT_SUCCEEDED;
    }

    public function toAmplitude(): array
    {
        return [
            'event_type'       => EventEnum::LOGOUT_SUCCEEDED,
            'event_properties' => [
                'user_id' => $this->user->id,
            ],
        ];
    }
}
