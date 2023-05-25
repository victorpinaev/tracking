<?php

namespace common\modules\tracking\events;

use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class LevelCalculationRequestedEvent extends BaseObject implements AmplitudeEventInterface
{
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
        return EventEnum::LEVEL_CALCULATION_REQUESTED;
    }

    public function toAmplitude(): array
    {
        return [
            'event_type'       => 'check_my_level',
            'event_properties' => [
                'retro_level' => $this->user->level_number,
            ],
        ];
    }
}
