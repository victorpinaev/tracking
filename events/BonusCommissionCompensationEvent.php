<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\models\Payments;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class BonusCommissionCompensationEvent extends BaseObject implements AmplitudeEventInterface
{
    public Payments $payment;
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
        return EventEnum::BONUS_COMMISSION_COMPENSATION;
    }

    public function toAmplitude(): array
    {
        $payment = $this->payment;

        $paymentCommission = $payment->amount - $payment->site_profit;

        $eventProperties = [
            'commission_compensation' => MoneyHelper::convertFromCents(-$paymentCommission),
        ];

        return [
            'event_type'       => EventEnum::BONUS_COMMISSION_COMPENSATION,
            'event_properties' => $eventProperties,
        ];
    }
}
