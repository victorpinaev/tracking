<?php

namespace common\modules\tracking\events;

use api\models\UserDrop;
use common\enums\SocketMessageEnum;
use common\helpers\ActiveRecordHelper;
use common\helpers\MoneyHelper;
use common\models\UsersDrop;
use common\models\UsersExchanges;
use common\modules\tracking\dtos\CentrifugoMessageDTO;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use common\modules\tracking\events\interfaces\CentrifugoEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class SkinExchangeCompletedEvent extends BaseObject implements AmplitudeEventInterface, CentrifugoEventInterface
{
    public UsersDrop $drop;
    public UsersExchanges $exchange;
    public array $resultDrops;

    public function getUser(): User
    {
        return $this->drop->user;
    }

    public function getInternalName(): string
    {
        return EventEnum::SKIN_EXCHANGE_COMPLETED;
    }

    public function toAmplitude(): array
    {
        $resultDropPrice = 0;
        $resultDropIds = $resultDropNames = [];

        foreach ($this->resultDrops as $resultDrop) {
            /* @var UsersDrop $resultDrop */
            $resultDropIds[] = $resultDrop->id;
            $resultDropNames[] = $resultDrop->item->market_hash_name;
            $resultDropPrice += $resultDrop->price;
        }
        $eventProperties = [
            'drop_id'           => $this->drop->id,
            'market_hash_name'  => $this->drop->item->market_hash_name,
            'result_drop_ids'   => $resultDropIds,
            'result_drop_names' => $resultDropNames,
            'result_drop_price' => MoneyHelper::convertFromCents($resultDropPrice),
            'exchange_id'       => $this->exchange->id,
        ];

        return [
            'event_type'       => 'skin_exchange_completed',
            'event_properties' => $eventProperties,
        ];
    }

    public function toCentrifugo(): array
    {
        $drop = ActiveRecordHelper::castTo(UserDrop::class, $this->drop);

        $messages[] = new CentrifugoMessageDTO([
            'name'   => SocketMessageEnum::DROP_UPDATE,
            'data'   => $drop->toArray([], UserDrop::DEFAULT_EXPAND),
            'params' => ['user_id' => $this->drop->user_id],
        ]);

        return $messages;
    }
}
