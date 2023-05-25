<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\models\upgrade\Upgrades;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

class GgrUpgradesEvent extends BaseObject implements AmplitudeEventInterface
{
    public Upgrades $upgrade;

    public function getUser(): User
    {
        return $this->upgrade->user;
    }

    public function getInternalName(): string
    {
        return EventEnum::GGR_UPGRADES;
    }

    public function toAmplitude(): array
    {
        $sumDropPrice = \array_sum(ArrayHelper::getColumn($this->upgrade->usedDrops, 'price'));
        $receivedDropPrice = $this->upgrade->drop_id ? $this->upgrade->drop->price : 0;

        $eventProperties = [
            'sum_drop_price-received_drop_price' => MoneyHelper::convertFromCents($sumDropPrice - $receivedDropPrice),
        ];

        return [
            'event_type'       => EventEnum::GGR_UPGRADES,
            'event_properties' => $eventProperties,
        ];
    }
}
