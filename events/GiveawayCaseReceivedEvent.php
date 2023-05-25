<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\models\UsersDrop;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\case\models\Cases;
use domain\user\models\User;
use yii\base\BaseObject;

class GiveawayCaseReceivedEvent extends BaseObject implements AmplitudeEventInterface
{
    public Cases $case;
    public UsersDrop $drop;

    public function getUser(): User
    {
        return $this->drop->user;
    }

    public function getInternalName(): string
    {
        return EventEnum::GIVEAWAY_CASE_RECEIVED;
    }

    public function toAmplitude(): array
    {
        return [
            'event_type'       => EventEnum::GIVEAWAY_CASE_RECEIVED,
            'event_properties' => [
                'case_id'         => $this->case->id . '_' . $this->case->name,
                'case_main_price' => MoneyHelper::convertFromCents($this->drop->caseOpen->generation->case_price),
                'drop_id'         => $this->drop->id,
                'drop_price'      => MoneyHelper::convertFromCents($this->drop->price),
            ],
        ];
    }
}
