<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\case\models\Cases;
use domain\user\models\User;
use yii\base\BaseObject;

class CaseUnfavoritedEvent extends BaseObject implements AmplitudeEventInterface
{
    public Cases $case;
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
        return EventEnum::CASE_UNFAVORITED;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'case_id'         => $this->case->id . '_' . $this->case->name,
            'case_main_price' => MoneyHelper::convertFromCents($this->case->price),
            'case_type'       => $this->case->mainSection->name ?? null,
        ];

        return [
            'event_type'       => 'case_unfavorited',
            'event_properties' => $eventProperties,
        ];
    }
}
