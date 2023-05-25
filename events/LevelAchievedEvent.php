<?php

namespace common\modules\tracking\events;

use common\modules\tracking\enums\DomainEventEnum;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use common\modules\tracking\events\interfaces\DomainEventInterface;
use domain\feast\models\FeastsOld;
use domain\user\models\User;
use yii\base\BaseObject;

class LevelAchievedEvent extends BaseObject implements
    AmplitudeEventInterface,
    DomainEventInterface
{
    public FeastsOld $feast;
    public int $level;

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
        return EventEnum::LEVEL_ACHIEVED;
    }

    public function getExternalName(): string
    {
        return DomainEventEnum::FEAST_LEVEL_REACHED;
    }

    public function toAmplitude(): array
    {
        return [
            'event_type'       => EventEnum::LEVEL_ACHIEVED,
            'event_properties' => [
                'feast_slug' => $this->feast->slug,
                'new_level'  => $this->level,
            ],
        ];
    }

    public function toDomain(): array
    {
        return [
            'player_id' => $this->getUser()->id,
            'feast_id'  => $this->feast->id,
            'level'     => $this->level,

            'player' => [
                'id' => $this->getUser()->id,
            ],
        ];
    }
}
