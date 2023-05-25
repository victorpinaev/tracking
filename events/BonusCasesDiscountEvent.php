<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class BonusCasesDiscountEvent extends BaseObject implements AmplitudeEventInterface
{
    public int $discountSum;
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
        return EventEnum::BONUS_CASES_DISCOUNT;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'discount_sum' => MoneyHelper::convertFromCents(-$this->discountSum),
        ];

        return [
            'event_type'       => EventEnum::BONUS_CASES_DISCOUNT,
            'event_properties' => $eventProperties,
        ];
    }
}
