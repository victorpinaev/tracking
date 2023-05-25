<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\models\Payments;
use common\models\TrackingSession;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeFinalEventInterface;
use common\modules\tracking\services\TrackingService;
use domain\user\models\User;
use yii\base\BaseObject;

class PaymentErrorEvent extends BaseObject implements AmplitudeFinalEventInterface
{
    public Payments $payment;

    public function getUser(): User
    {
        return $this->payment->user;
    }

    public function getInternalName(): string
    {
        return EventEnum::PAYMENT_ERROR;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'payment_id'     => $this->payment->id,
            'external_id'    => $this->payment->external_id,
            'payment_sum'    => MoneyHelper::convertFromCents($this->payment->amount),
            'error_id'       => $this->payment->getErrorMessage(),
            'payment_system' => $this->payment->system,
            'payment_method' => $this->payment->additionalData->paymentType ?? null,
        ];

        return [
            'event_type'       => 'payment_error',
            'event_properties' => $eventProperties,
        ];
    }

    public function restoreAmpSession(string $initialName): ?TrackingSession
    {
        return TrackingService::instance()->findAndRemoveAmpSession($initialName, $this->payment->id);
    }
}
