<?php

namespace common\modules\tracking\events;

use api\models\UserDrop;
use common\enums\SocketMessageEnum;
use common\helpers\ActiveRecordHelper;
use common\models\UsersDrop;
use common\modules\tracking\dtos\CentrifugoMessageDTO;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\CentrifugoEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class HypeDropCreatedEvent extends BaseObject implements CentrifugoEventInterface
{
    public UsersDrop $drop;
    public string $hypeType;

    public function getUser(): User
    {
        return $this->drop->user;
    }

    public function getInternalName(): string
    {
        return EventEnum::HYPE_DROP_CREATED;
    }

    public function toCentrifugo(): array
    {
        $payload = ActiveRecordHelper::castTo(UserDrop::class, $this->drop)->toArray([], UserDrop::DEFAULT_EXPAND);

        $payload['hype_type'] = $this->hypeType; // Add hype type to standard drop payload (for "wow!" phrases).
        $payload['hype_profit'] = $this->drop->getProfit();

        $messages[] = new CentrifugoMessageDTO([
            'name'  => SocketMessageEnum::HYPE_DROP_NEW,
            'data'  => $payload,
            'delay' => 15000, // 10 sec + extra delay to finish animation on FE side.
        ]);

        return $messages;
    }
}
