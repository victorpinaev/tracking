<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\models\UsersDrop;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class ProfitWithdrawalsEvent extends BaseObject implements AmplitudeEventInterface
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
        return EventEnum::PROFIT_WITHDRAWALS;
    }

    public function toAmplitude(): array
    {
        $marketPrice = $this->drop->itemEntity->price ?? 0;

        $eventProperties = [
            'drop_price-market_price' => MoneyHelper::convertFromCents($this->drop->price - $marketPrice),
        ];

        return [
            'event_type'       => EventEnum::PROFIT_WITHDRAWALS,
            'event_properties' => $eventProperties,
        ];
    }
}
