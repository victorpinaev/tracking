<?php

namespace common\modules\tracking\events;

use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use common\modules\tracking\events\interfaces\GoogleAnalyticsEventInterface;
use common\modules\tracking\events\interfaces\TapfiliateEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class AccountCreatedEvent extends BaseObject implements
    AmplitudeEventInterface,
    GoogleAnalyticsEventInterface,
    TapfiliateEventInterface
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
        return EventEnum::ACCOUNT_CREATED;
    }

    public function toAmplitude(): array
    {
        return [
            'event_type'       => EventEnum::ACCOUNT_CREATED,
            'event_properties' => [
                'user_id' => $this->user->id,
            ],
        ];
    }

    public function toGoogleAnalytics(): array
    {
        return [
            'uid' => $this->user->id,
            'ec'  => 'Регистрация',
            'ea'  => 'Успешно',
        ];
    }

    public function toTapfiliate(): array
    {
        return [
            'customer_id' => (string) $this->getUser()->id,
            'status'      => 'new',
        ];
    }
}
