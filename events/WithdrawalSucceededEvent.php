<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\models\TrackingSession;
use common\models\UsersDrop;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeFinalEventInterface;
use common\modules\tracking\events\interfaces\GoogleAnalyticsEventInterface;
use common\modules\tracking\services\TrackingService;
use domain\user\models\User;
use yii\base\BaseObject;

class WithdrawalSucceededEvent extends BaseObject implements
    AmplitudeFinalEventInterface,
    GoogleAnalyticsEventInterface
{
    public UsersDrop $drop;

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
        return EventEnum::ITEM_WITHDRAWAL;
    }

    public function toAmplitude(): array
    {
        $itemEntityMarket = $this->drop->itemEntity->market ?? '-';
        $marketPrice = MoneyHelper::convertFromCents($this->drop->itemEntity->price ?? null);
        $minusMarketPrice = $marketPrice ? -$marketPrice : 0;

        $eventProperties = [
            'drop_id'          => $this->drop->id,
            'market'           => $itemEntityMarket,
            'market_hash_name' => $this->drop->item->market_hash_name,
            'market_price'     => $marketPrice,
            'drop_price'       => MoneyHelper::convertFromCents($this->drop->price),
            '$revenue'         => $minusMarketPrice,
            'price'            => $minusMarketPrice,
            '$quantity'        => 1,
            'revenueType'      => 'withdrawal',
            'reason_type'      => $this->drop->type,
        ];

        return [
            'event_type'       => 'withdrawal_succeeded',
            'event_properties' => $eventProperties,
        ];
    }

    public function toGoogleAnalytics(): array
    {
        $withdrawalAmount = MoneyHelper::convertFromCents($this->drop->price);

        $reqStaticData = [
            'ec'  => 'Выигрыш',
            'ea'  => 'Вывод',
            'cm5' => 1,
        ];

        $reqDynamicData = [
            'cm6' => $withdrawalAmount,   // сумма вывода
        ];

        return \array_merge($reqStaticData, $reqDynamicData);
    }

    public function restoreAmpSession(string $initialName): ?TrackingSession
    {
        return TrackingService::instance()->findAndRemoveAmpSession($initialName, $this->drop->id);
    }
}
