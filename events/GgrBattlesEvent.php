<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\battle\models\Battle;
use domain\user\models\User;
use yii\base\BaseObject;

class GgrBattlesEvent extends BaseObject implements AmplitudeEventInterface
{
    public Battle $battle;
    private User $user;

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getInternalName(): string
    {
        return EventEnum::GGR_BATTLES;
    }

    public function toAmplitude(): array
    {
        $winSum = $this->user->id === $this->battle->winner_id ? $this->battle->win_sum : 0;

        $eventProperties = [
            'fixed_price-win_sum' => MoneyHelper::convertFromCents($this->battle->fixed_price - $winSum),
        ];

        return [
            'event_type'       => EventEnum::GGR_BATTLES,
            'event_properties' => $eventProperties,
        ];
    }
}
