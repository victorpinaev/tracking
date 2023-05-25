<?php

namespace common\modules\tracking\events;

use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\case\models\Cases;
use domain\user\models\User;
use yii\base\BaseObject;

class GiveawayCaseParticipationEvent extends BaseObject implements AmplitudeEventInterface
{
    public Cases $case;

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
        return EventEnum::GIVEAWAY_CASE_PARTICIPATION;
    }

    public function toAmplitude(): array
    {
        return [
            'event_type'       => EventEnum::GIVEAWAY_CASE_PARTICIPATION,
            'event_properties' => [
                'case_id' => $this->case->id . '_' . $this->case->name,
            ],
        ];
    }
}
