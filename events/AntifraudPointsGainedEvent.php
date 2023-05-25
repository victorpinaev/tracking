<?php

namespace common\modules\tracking\events;

use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class AntifraudPointsGainedEvent extends BaseObject implements AmplitudeEventInterface
{
    public int $amount;
    public string $checkName;
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
        return EventEnum::ANTIFRAUD_POINTS_GAINED;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'points_gained' => $this->amount,
            'check_name'    => $this->checkName,
        ];

        $userProperties = [
            'total_antifraud_points' => $this->user->antifraudPoints->points ?? 0,
        ];

        return [
            'event_type'       => 'antifraud_points_gained',
            'event_properties' => $eventProperties,
            'user_properties'  => $userProperties,
        ];
    }
}
