<?php

namespace common\modules\tracking\events;

use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\enums\UserRoleEnum;
use domain\user\models\User;
use yii\base\BaseObject;

class RoleChangedEvent extends BaseObject implements AmplitudeEventInterface
{
    public string $changedRole;

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
        return EventEnum::USER_ROLE_CHANGED;
    }

    public function toAmplitude(): array
    {
        $newRole = \implode(', ', $this->user->roleNames);
        $oldRole = $this->getOldRoleName();

        return [
            'event_type'       => EventEnum::USER_ROLE_CHANGED,
            'event_properties' => [
                'new_role' => $newRole,
                'old_role' => $oldRole,
            ],
        ];
    }

    private function getOldRoleName(): string
    {
        $oldRole = clone $this->user->roles;
        $oldRole->{$this->changedRole} = !$oldRole->{$this->changedRole};

        return \implode(', ', UserRoleEnum::roleNames($oldRole));
    }
}
