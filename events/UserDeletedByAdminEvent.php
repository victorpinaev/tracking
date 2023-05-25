<?php

namespace common\modules\tracking\events;

use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class UserDeletedByAdminEvent extends BaseObject implements AmplitudeEventInterface
{
    public int $adminId;
    public User $user;

    public function getUser(): User
    {
        return $this->user;
    }

    public function getInternalName(): string
    {
        return EventEnum::USER_DELETED_BY_ADMIN;
    }

    public function toAmplitude(): array
    {
        return [
            'event_type'       => 'user_deleted_by_admin',
            'event_properties' => [
                'admin_id' => $this->adminId,
            ],
        ];
    }
}
