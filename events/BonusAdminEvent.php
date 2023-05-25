<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class BonusAdminEvent extends BaseObject implements AmplitudeEventInterface
{
    public int $amount;
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
        return EventEnum::BONUS_ADMIN;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'money' => MoneyHelper::convertFromCents(-$this->amount),
        ];

        return [
            'event_type'       => EventEnum::BONUS_ADMIN,
            'event_properties' => $eventProperties,
        ];
    }
}
