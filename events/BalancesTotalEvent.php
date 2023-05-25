<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\models\UsersDrop;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\models\Profile;
use domain\user\models\User;
use yii\base\BaseObject;

class BalancesTotalEvent extends BaseObject implements AmplitudeEventInterface
{
    public function getUser(): User
    {
        $fakeUser = new User();
        $fakeUser->id = 101; // this is fake id for internal usage

        return $fakeUser;
    }

    public function getInternalName(): string
    {
        return EventEnum::BALANCES_TOTAL;
    }

    public function toAmplitude(): array
    {
        $cashBalance = Profile::find()
            ->joinWith('user.roles', false)
            ->andWhere('IFNULL(`user_roles`.`is_blogger`, FALSE) = false')
            ->andWhere('IFNULL(`user_roles`.`is_staff`, FALSE) = false')
            ->andWhere('IFNULL(`user_roles`.`is_bot`, FALSE) = false')
            ->sum('wallet')
        ;

        $dropBalance = UsersDrop::find()
            ->joinWith('user.roles', false)
            ->andWhere('IFNULL(`user_roles`.`is_blogger`, FALSE) = false')
            ->andWhere('IFNULL(`user_roles`.`is_staff`, FALSE) = false')
            ->andWhere('IFNULL(`user_roles`.`is_bot`, FALSE) = false')
            ->andWhere(['in', 'status', UsersDrop::SKIN_BALANCE_STATUS_LIST])
            ->sum('price')
        ;

        $eventProperties = [
            'cash_balance'  => MoneyHelper::convertFromCents($cashBalance),
            'items_balance' => MoneyHelper::convertFromCents($dropBalance),
        ];

        return [
            'event_type'       => EventEnum::BALANCES_TOTAL,
            'event_properties' => $eventProperties,
        ];
    }
}
