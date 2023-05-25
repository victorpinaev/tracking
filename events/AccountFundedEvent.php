<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\models\Payments;
use common\models\PromocodesUsages;
use common\models\TrackingSession;
use common\modules\tracking\enums\DomainEventEnum;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeFinalEventInterface;
use common\modules\tracking\events\interfaces\DomainEventInterface;
use common\modules\tracking\events\interfaces\FacebookPixelEventInterface;
use common\modules\tracking\events\interfaces\GoogleAnalyticsEventInterface;
use common\modules\tracking\events\interfaces\TapfiliateEventInterface;
use common\modules\tracking\services\TrackingService;
use domain\user\models\User;
use yii\base\BaseObject;

class AccountFundedEvent extends BaseObject implements
    AmplitudeFinalEventInterface,
    GoogleAnalyticsEventInterface,
    FacebookPixelEventInterface,
    DomainEventInterface,
    TapfiliateEventInterface
{
    public Payments $payment;
    public PromocodesUsages|null $promocodesUsage = null;
    private int $paymentNumber;
    private User $user;

    public function init(): void
    {
        $this->paymentNumber = Payments::getCountUserPayments($this->user);

        parent::init();
    }

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
        return EventEnum::ACCOUNT_FUNDED;
    }

    public function getExternalName(): string
    {
        return DomainEventEnum::PAYMENT_SUCCEEDED;
    }

    public function toAmplitude(): array
    {
        $payment = $this->payment;
        $paymentCommission = $payment->amount - $payment->site_profit;
        $promocodeBonus = $this->promocodesUsage ? $this->promocodesUsage->getBonusByType($payment->amount) : null;

        $eventProperties = [
            'payment_id'              => $payment->id,
            'external_id'             => $payment->external_id,
            'payment_sum'             => MoneyHelper::convertFromCents($payment->amount),
            'site_payment_commission' => MoneyHelper::convertFromCents($paymentCommission),
            'promocode_id'            => $this->promocodesUsage->promocode->id        ?? null,
            'promocode_name'          => $this->promocodesUsage->promocode->code      ?? null,
            'tech_name'               => $this->promocodesUsage->promocode->tech_name ?? null,
            'promocode_payment_bonus' => $promocodeBonus ? MoneyHelper::convertFromCents($promocodeBonus) : null,
            'payment_system'          => $payment->system,
            'payment_number'          => $this->paymentNumber,
            'payment_method'          => $payment->additionalData->enhancedPaymentType ?? null,
            '$revenue'                => MoneyHelper::convertFromCents($this->payment->site_profit),
            'price'                   => MoneyHelper::convertFromCents($this->payment->site_profit),
            '$quantity'               => 1,
            'revenueType'             => 'deposit',
        ];

        return [
            'event_type'       => 'payment_succeded',
            'event_properties' => $eventProperties,
        ];
    }

    public function toGoogleAnalytics(): array
    {
        $paymentId = $this->payment->id;

        $paymentAmount = MoneyHelper::convertFromCents($this->payment->amount);
        $paymentDesc = "Пополнение {$this->payment->system}";

        $isFirstPayment = (1 === $this->paymentNumber);
        $paymentAmountFirst = $isFirstPayment ? $paymentAmount : 0;

        $paymentType = $isFirstPayment ? 'Первая' : 'Повторная';
        $paymentTypeBin = $isFirstPayment ? 1 : 0;

        $reqStaticData = [
            'ec'    => 'Оплата',
            'pa'    => 'purchase',
            'pr1qt' => 1,
        ];
        $reqDynamicData = [
            'ea'    => $paymentType,         // если это первая оплата пользователя передаем Первая, иначе Повторная
            'el'    => $paymentDesc,         // название способа пополнения
            'ti'    => $paymentId,           // идентификатор оплаты, обязательный параметр
            'tr'    => $paymentAmount,       // сумма оплаты
            'pr1nm' => $paymentDesc,      // название способа пополнения
            'pr1pr' => $paymentAmount,    // сумма оплаты
            'cm1'   => $paymentTypeBin,     // если это первая оплата пользователя передаем 1, иначе передаем 0
            'cm2'   => $paymentAmountFirst, // если это первая оплата пользователя передаем сумму оплаты, иначе передаем 0
        ];

        return \array_merge($reqStaticData, $reqDynamicData);
    }

    public function toFacebookPixel(): array
    {
        return [
            'event_name' => 'Purchase',
            'amount'     => $this->payment->amount,
        ];
    }

    public function toDomain(): array
    {
        return [
            'player_id'   => $this->getUser()->id,
            'main_amount' => $this->payment->amount,
            'gateway'     => $this->payment->system,

            'player' => [
                'id' => $this->getUser()->id,
            ],
        ];
    }

    public function toTapfiliate(): array
    {
        return [
            'customer_id' => (string) $this->getUser()->id,
            'external_id' => (string) $this->payment->id,
            'amount'      => MoneyHelper::convertFromCents($this->payment->amount),
        ];
    }

    public function restoreAmpSession(string $initialName): ?TrackingSession
    {
        return TrackingService::instance()->findAndRemoveAmpSession($initialName, $this->payment->id);
    }
}
