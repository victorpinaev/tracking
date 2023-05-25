<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\models\User;
use frontend\models\UsersDrop;
use yii\base\BaseObject;

class GgrCasesEvent extends BaseObject implements AmplitudeEventInterface
{
    public UsersDrop $drop;

    public function getUser(): User
    {
        return $this->drop->user;
    }

    public function getInternalName(): string
    {
        return EventEnum::GGR_CASES;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'case_main_price-drop_price' => MoneyHelper::convertFromCents($this->drop->caseOpen->generation->case_price - $this->drop->price),
        ];

        return [
            'event_type'       => EventEnum::GGR_CASES,
            'event_properties' => $eventProperties,
        ];
    }
}
