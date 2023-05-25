<?php

namespace common\modules\tracking\events;

use common\models\TrackingSession;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeFinalEventInterface;
use common\modules\tracking\services\TrackingService;
use domain\battle\models\Battle;
use domain\user\models\User;
use yii\base\BaseObject;

class BattleLeftEvent extends BaseObject implements AmplitudeFinalEventInterface
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
        return EventEnum::BATTLE_LEFT;
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

    public function restoreAmpSession(string $initialName): ?TrackingSession
    {
        return TrackingService::instance()->findAndRemoveAmpSession($initialName, $this->battle->id, $this->user->id);
    }
}
