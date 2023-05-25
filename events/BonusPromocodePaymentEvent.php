<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class BonusPromocodePaymentEvent extends BaseObject implements AmplitudeEventInterface
{
    public int $promocodeBonus;
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
        return EventEnum::BONUS_PROMOCODE_PAYMENT_BONUS;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'promocode_payment_bonus' => MoneyHelper::convertFromCents(-$this->promocodeBonus),
        ];

        return [
            'event_type'       => EventEnum::BONUS_PROMOCODE_PAYMENT_BONUS,
            'event_properties' => $eventProperties,
        ];
    }
}
