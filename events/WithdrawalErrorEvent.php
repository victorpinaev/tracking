<?php

namespace common\modules\tracking\events;

use common\enums\SocketMessageEnum;
use common\models\TrackingSession;
use common\models\Trades;
use common\models\UsersDrop;
use common\modules\tracking\dtos\CentrifugoMessageDTO;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeFinalEventInterface;
use common\modules\tracking\events\interfaces\CentrifugoEventInterface;
use common\modules\tracking\services\TrackingService;
use domain\user\models\User;
use yii\base\BaseObject;

class WithdrawalErrorEvent extends BaseObject implements
    AmplitudeFinalEventInterface,
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
        return EventEnum::WITHDRAWAL_ERROR;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'drop_id'  => $this->drop->id,
            'error_id' => $this->trade->error_code . '/' . $this->trade->status_details,
        ];

        return [
            'event_type'       => 'withdrawal_error',
            'event_properties' => $eventProperties,
        ];
    }

    public function restoreAmpSession(string $initialName): ?TrackingSession
    {
        return TrackingService::instance()->findAndRemoveAmpSession($initialName, $this->drop->id);
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
