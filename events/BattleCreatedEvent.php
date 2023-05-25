<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\battle\models\Battle;
use domain\user\models\User;
use yii\base\BaseObject;

class BattleCreatedEvent extends BaseObject implements AmplitudeEventInterface
{
    public Battle $battle;

    public function getUser(): User
    {
        return $this->battle->creator;
    }

    public function getInternalName(): string
    {
        return EventEnum::BATTLE_CREATED;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'battle_id'    => $this->battle->id,
            'is_public'    => $this->battle->is_public,
            'battle_cost'  => MoneyHelper::convertFromCents($this->battle->fixed_price),
            'playlist'     => $this->composeBattlePlaylist(),
            'participants' => $this->battle->max_player_amount,
            'rounds'       => $this->calculateBattleRounds(),
        ];

        return [
            'event_type'       => $this->getInternalName(),
            'event_properties' => $eventProperties,
        ];
    }

    private function composeBattlePlaylist(): array
    {
        $playlist = [];

        foreach ($this->battle->cases as $case) {
            $playlist[] = $case->id . '_' . $case->name;
        }

        return $playlist;
    }

    private function calculateBattleRounds(): int
    {
        $rounds = 0;

        foreach ($this->battle->battleCases as $pivot) {
            $rounds += $pivot->quantity;
        }

        return $rounds;
    }
}
