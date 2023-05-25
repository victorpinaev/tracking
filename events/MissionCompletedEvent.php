<?php

namespace common\modules\tracking\events;

use common\enums\SocketMessageEnum;
use common\helpers\MoneyHelper;
use common\modules\prize\interfaces\PrizeEntityInterface;
use common\modules\tracking\dtos\CentrifugoMessageDTO;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use common\modules\tracking\events\interfaces\CentrifugoEventInterface;
use domain\user\models\User;
use yii\base\BaseObject;

class MissionCompletedEvent extends BaseObject implements AmplitudeEventInterface, CentrifugoEventInterface
{
    public string $missionId;
    public string $prizeType;
    public int $prizePrice;
    public PrizeEntityInterface $prizeEntity;

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
        return EventEnum::MISSION_PRIZE_TAKEN;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'mission_id'  => $this->missionId,
            'prize_type'  => $this->prizeType,
            'prize_price' => MoneyHelper::convertFromCents($this->prizePrice),
        ];

        return [
            'event_type'       => $this->getInternalName(),
            'event_properties' => $eventProperties,
        ];
    }

    public function toCentrifugo(): array
    {
        $messages[] = new CentrifugoMessageDTO([
            'name' => SocketMessageEnum::MISSION_PRIZE_DELIVERED,
            'data' => [
                'mission_id'   => $this->missionId,
                'prize_type'   => $this->prizeType,
                'prize_price'  => $this->prizePrice,
                'prize_entity' => $this->prizeEntity->toArray(),
            ],
            'params' => ['user_id' => $this->getUser()->id],
            'delay'  => 15000,
        ]);

        return $messages;
    }
}
