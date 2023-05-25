<?php

namespace common\modules\tracking\events;

use api\models\UserDrop;
use common\enums\SocketMessageEnum;
use common\helpers\ActiveRecordHelper;
use common\models\UsersDrop;
use common\modules\tracking\dtos\CentrifugoMessageDTO;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeInitialEventInterface;
use common\modules\tracking\events\interfaces\CentrifugoEventInterface;
use common\modules\tracking\services\TrackingService;
use domain\user\models\User;
use yii\base\BaseObject;

class WithdrawalStarted extends BaseObject implements AmplitudeInitialEventInterface, CentrifugoEventInterface
{
    public UsersDrop $drop;

    public function getUser(): User
    {
        return $this->drop->user;
    }

    public function getInternalName(): string
    {
        return EventEnum::WITHDRAWAL_STARTED;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'drop_id'          => $this->drop->id,
            'drop_price'       => $this->drop->price,
            'market_hash_name' => $this->drop->item->market_hash_name,
        ];

        return [
            'event_type'       => $this->getInternalName(),
            'event_properties' => $eventProperties,
        ];
    }

    public function saveAmpSession(string $sessionId): void
    {
        TrackingService::instance()->saveAmpSession($this->getInternalName(), $sessionId, $this->drop->id);
    }

    public function toCentrifugo(): array
    {
        $messages[] = new CentrifugoMessageDTO([
            'name'   => SocketMessageEnum::SKIN_BALANCE_UPDATE,
            'data'   => ['amount' => \frontend\models\UsersDrop::pendingPrice($this->drop->user_id)],
            'params' => ['user_id' => $this->drop->user_id],
        ]);

        $drop = ActiveRecordHelper::castTo(UserDrop::class, $this->drop);

        $messages[] = new CentrifugoMessageDTO([
            'name'   => SocketMessageEnum::DROP_UPDATE,
            'data'   => $drop->toArray([], UserDrop::DEFAULT_EXPAND),
            'params' => ['user_id' => $this->drop->user_id],
        ]);

        return $messages;
    }
}
