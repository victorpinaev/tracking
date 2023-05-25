<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\models\Leaderboard;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\feast\services\FeastProgressService;
use domain\user\models\User;
use yii\base\BaseObject;

class LeaderboardTierChangedEvent extends BaseObject implements AmplitudeEventInterface
{
    public Leaderboard $leaderboard;

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
        return EventEnum::LEADERBOARD_TIER_CHANGED;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'feast_slug'         => $this->leaderboard->feast->slug ?? null,
            'current_user_level' => FeastProgressService::getAbsoluteUserLevel($this->user, $this->leaderboard->feast),
            'tier_name'          => MoneyHelper::format($this->leaderboard->min_deposit) . '-' . MoneyHelper::format($this->leaderboard->max_deposit),
            'leaderboard_label'  => $this->leaderboard->group_label,
        ];

        return [
            'event_type'       => EventEnum::LEADERBOARD_TIER_CHANGED,
            'event_properties' => $eventProperties,
        ];
    }
}
