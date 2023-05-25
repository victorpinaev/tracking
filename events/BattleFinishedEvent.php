<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\battle\models\Battle;
use domain\user\enums\UserRoleEnum;
use domain\user\models\User;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

class BattleFinishedEvent extends BaseObject implements AmplitudeEventInterface
{
    public Battle $battle;

    public function getUser(): User
    {
        return $this->battle->creator;
    }

    public function getInternalName(): string
    {
        return EventEnum::BATTLE_FINISHED;
    }

    public function toAmplitude(): array
    {
        $participantCount = \count($this->battle->battleUsers);

        $botCount = (int) User::find()
            ->andHasRole(UserRoleEnum::BOT)
            ->andWhere(['id' => ArrayHelper::getColumn($this->battle->battleUsers, 'user_id')])
            ->count()
        ;

        $eventProperties = [
            'battle_id' => $this->battle->id,
            'winner_id' => $this->battle->winner_id,
            'win_sum'   => MoneyHelper::convertFromCents($this->battle->win_sum),

            'drop_ids'        => ArrayHelper::getColumn($this->battle->battleSteps, 'drop_id'),
            'participant_ids' => ArrayHelper::getColumn($this->battle->battleUsers, 'user_id'),

            'participants_count' => $participantCount,
            'bots_count'         => $botCount,
            'total_battle_cost'  => MoneyHelper::convertFromCents($participantCount * $this->battle->fixed_price),
        ];

        return [
            'event_type'       => 'battle_finished',
            'event_properties' => $eventProperties,
        ];
    }
}
