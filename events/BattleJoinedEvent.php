<?php

namespace common\modules\tracking\events;

use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeInitialEventInterface;
use common\modules\tracking\services\TrackingService;
use domain\battle\models\Battle;
use domain\user\models\User;
use yii\base\BaseObject;

class BattleJoinedEvent extends BaseObject implements AmplitudeInitialEventInterface
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
        return EventEnum::BATTLE_JOINED;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'battle_id' => $this->battle->id,
        ];

        return [
            'event_type'       => $this->getInternalName(),
            'event_properties' => $eventProperties,
        ];
    }

    public function saveAmpSession(string $sessionId): void
    {
        TrackingService::instance()->saveAmpSession(
            $this->getInternalName(),
            $sessionId,
            $this->battle->id,
            $this->user->id
        );
    }
}
