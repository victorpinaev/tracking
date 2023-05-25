<?php

namespace common\modules\tracking\events;

use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\enums\ProfileStatusEnum;
use domain\user\models\User;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

class UserStatusChangedEvent extends BaseObject implements AmplitudeEventInterface
{
    public int $oldStatus;
    public int $newStatus;

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
        return EventEnum::USER_STATUS_CHANGED;
    }

    /**
     * @throws \Exception
     */
    public function toAmplitude(): array
    {
        $oldStatus = ArrayHelper::getValue(ProfileStatusEnum::names(), $this->oldStatus, "Unknown({$this->oldStatus})");
        $newStatus = ArrayHelper::getValue(ProfileStatusEnum::names(), $this->newStatus, "Unknown({$this->newStatus})");

        return [
            'event_type'       => EventEnum::USER_STATUS_CHANGED,
            'event_properties' => [
                'new_status' => $newStatus,
                'old_status' => $oldStatus,
            ],
            'user_properties' => [
                'user_status' => $newStatus,
            ],
        ];
    }
}
