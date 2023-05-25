<?php

namespace common\modules\tracking\events;

use api\models\UserDrop;
use common\enums\SocketMessageEnum;
use common\helpers\ActiveRecordHelper;
use common\helpers\MoneyHelper;
use common\models\UsersDrop;
use common\modules\tracking\dtos\CentrifugoMessageDTO;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use common\modules\tracking\events\interfaces\CentrifugoEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class SkinReceivedEvent extends BaseObject implements AmplitudeEventInterface, CentrifugoEventInterface
{
    public UsersDrop|UserDrop $drop; // TODO: collapse different models later!

    public function getUser(): User
    {
        return $this->drop->user;
    }

    public function getInternalName(): string
    {
        return EventEnum::SKIN_RECEIVED;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'drop_id'          => $this->drop->id,
            'reason_id'        => $this->drop->event_id,
            'reason_type'      => $this->drop->type,
            'drop_price'       => MoneyHelper::convertFromCents($this->drop->price),
            'market_hash_name' => $this->drop->item->market_hash_name,
        ];

        return [
            'event_type'       => 'skin_received',
            'event_properties' => $eventProperties,
        ];
    }

    public function toCentrifugo(): array
    {
        /** @var UserDrop $drop */
        $drop = ActiveRecordHelper::castTo(UserDrop::class, $this->drop)->fresh(UserDrop::DEFAULT_EXPAND);

        $showInLiveDrop = true;

        if (isset($drop->caseOpen->case->level)) {
            $showInLiveDrop = false; // Do not display drop from daily cases in livedrop.
        }

        if ($showInLiveDrop) {
            $messages[] = new CentrifugoMessageDTO([
                'name'  => SocketMessageEnum::DROP_NEW,
                'data'  => $drop->toArray([], UserDrop::DEFAULT_EXPAND),
                'delay' => 15000,
            ]);
        }

        $messages[] = new CentrifugoMessageDTO([
            'name'   => SocketMessageEnum::SKIN_BALANCE_UPDATE,
            'data'   => ['amount' => \frontend\models\UsersDrop::pendingPrice($this->drop->user_id)],
            'params' => ['user_id' => $this->drop->user_id],
        ]);

        return $messages;
    }
}
