<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\models\UsersDrop;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class BonusGiveawayCaseEvent extends BaseObject implements AmplitudeEventInterface
{
    public UsersDrop $drop;

    public function getUser(): User
    {
        return $this->drop->user;
    }

    public function getInternalName(): string
    {
        return EventEnum::BONUS_GIVEAWAY_CASE;
    }

    public function toAmplitude(): array
    {
        return [
            'event_type'       => EventEnum::BONUS_GIVEAWAY_CASE,
            'event_properties' => [
                'drop_price' => MoneyHelper::convertFromCents(-$this->drop->price),
            ],
        ];
    }
}
