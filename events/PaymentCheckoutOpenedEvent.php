<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeInitialEventInterface;
use common\modules\tracking\services\TrackingService;
use domain\user\models\User;
use yii\base\BaseObject;

class PaymentCheckoutOpenedEvent extends BaseObject implements AmplitudeInitialEventInterface
{
    public int $paymentId;
    public int|null $paymentSum = null; // For skin-based gateways we don't know an initial amount.
    public string $paymentSystem;

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
        return EventEnum::PAYMENT_CHECKOUT_OPENED;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'payment_system' => $this->paymentSystem,
            'payment_id'     => $this->paymentId,
        ];

        if ($this->paymentSum) {
            $eventProperties['payment_sum'] = MoneyHelper::convertFromCents($this->paymentSum);
        }

        return [
            'event_type'       => $this->getInternalName(),
            'event_properties' => $eventProperties,
        ];
    }

    public function saveAmpSession(string $sessionId): void
    {
        TrackingService::instance()->saveAmpSession($this->getInternalName(), $sessionId, $this->paymentId);
    }
}
