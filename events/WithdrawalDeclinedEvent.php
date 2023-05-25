<?php

namespace common\modules\tracking\events;

use common\enums\SocketMessageEnum;
use common\models\Trades;
use common\models\UsersDrop;
use common\modules\tracking\dtos\CentrifugoMessageDTO;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use common\modules\tracking\events\interfaces\CentrifugoEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class WithdrawalDeclinedEvent extends BaseObject implements
    AmplitudeEventInterface,
    CentrifugoEventInterface
{
    public UsersDrop $drop;
    public Trades $trade;

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
        return EventEnum::WITHDRAWAL_DECLINED;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'drop_id'  => $this->drop->id,
            'error_id' => $this->trade->error_code . '/' . $this->trade->status_details,
        ];

        return [
            'event_type'       => 'withdrawal_declined',
            'event_properties' => $eventProperties,
        ];
    }

    public function toCentrifugo(): array
    {
        $messages[] = new CentrifugoMessageDTO([
            'name'   => SocketMessageEnum::SKIN_BALANCE_UPDATE,
            'data'   => ['amount' => \frontend\models\UsersDrop::pendingPrice($this->user->id)],
            'params' => ['user_id' => $this->user->id],
        ]);

        return $messages;
    }
}
