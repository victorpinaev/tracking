<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\models\Promocodes;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class PromocodeSucceededEvent extends BaseObject implements AmplitudeEventInterface
{
    public Promocodes $promocode;
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
        return EventEnum::PROMOCODE_SUCCEEDED;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'promocode_type' => $this->promocode->type,
            'promocode_id'   => $this->promocode->id,
            'promocode_name' => $this->promocode->code,
            'tech_name'      => $this->promocode->tech_name,
            'promocode_n'    => MoneyHelper::convertFromCents($this->promocode->value_n),
            'promocode_k'    => $this->promocode->value_k,
        ];

        return [
            'event_type'       => 'promocode_succeded',
            'event_properties' => $eventProperties,
        ];
    }
}
