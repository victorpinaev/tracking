<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class PendingDropSumEvent extends BaseObject implements AmplitudeEventInterface
{
    public int $dropTotalPrice;
    public int $hotUserDropTotalPrice;

    public function getUser(): User
    {
        $fakeUser = new User();
        $fakeUser->id = 101; // this is fake id for internal usage

        return $fakeUser;
    }

    public function getInternalName(): string
    {
        return EventEnum::PENDING_DROP_SUM_UPD;
    }

    public function toAmplitude(): array
    {
        return [
            'event_type'       => EventEnum::PENDING_DROP_SUM_UPD,
            'event_properties' => [
                'total_price'       => MoneyHelper::convertFromCents($this->dropTotalPrice),
                'total_price_fd_2h' => MoneyHelper::convertFromCents($this->hotUserDropTotalPrice),
            ],
        ];
    }
}
