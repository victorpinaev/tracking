<?php

namespace common\modules\tracking\events;

use common\enums\SocketMessageEnum;
use common\modules\tracking\dtos\CentrifugoMessageDTO;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use common\modules\tracking\events\interfaces\CentrifugoEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class LevelUpEvent extends BaseObject implements CentrifugoEventInterface, AmplitudeEventInterface
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
        return EventEnum::LEVEL_UP;
    }

    public function toCentrifugo(): array
    {
        $messages = [];

        $nextLevel = null; // Null if there is no next level.

        if ($this->user->level->nextLevel) {
            $nextLevel = [
                'deposit_amount' => $this->user->level->nextLevel->deposit_amount,
                'level_number'   => $this->user->level->nextLevel->level_number,
                'xp'             => $this->user->level->nextLevel->xp,
            ];
        }

        $messages[] = new CentrifugoMessageDTO([
            'name' => SocketMessageEnum::LEVEL_UP,
            'data' => [
                'xp'           => $this->user->xp,
                'level_number' => $this->user->level_number,
                'level'        => [
                    'deposit_amount' => $this->user->level->deposit_amount,
                    'level_number'   => $this->user->level->level_number,
                    'xp'             => $this->user->level->xp,
                    'nextLevel'      => $nextLevel,
                ],
            ],
            'params' => [
                'user_id' => $this->user->id,
            ],
        ]);

        return $messages;
    }

    public function toAmplitude(): array
    {
        return [
            'event_type'       => 'level_up',
            'event_properties' => [
                'new_level' => $this->user->level_number,
            ],
        ];
    }
}
