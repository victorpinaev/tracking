<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\models\Promocodes;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class BonusPromocodeMoneyEvent extends BaseObject implements AmplitudeEventInterface
{
    public Promocodes $promocode;
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
        return EventEnum::BONUS_PROMOCODE_MONEY;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'promocode_n' => MoneyHelper::convertFromCents(-$this->promocode->value_n),
        ];

        return [
            'event_type'       => EventEnum::BONUS_PROMOCODE_MONEY,
            'event_properties' => $eventProperties,
        ];
    }
}
