<?php

namespace common\modules\tracking\events;

use common\modules\tracking\enums\DomainEventEnum;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\DomainEventInterface;
use domain\feast\models\FeastsGiveawaysOld;
use domain\user\models\User;
use yii\base\BaseObject;

class TicketSpentEvent extends BaseObject implements DomainEventInterface
{
    public FeastsGiveawaysOld $giveaway;
    public int $amount;

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
        return EventEnum::TICKET_SPENT;
    }

    public function getExternalName(): string
    {
        return DomainEventEnum::TICKET_SPENT;
    }

    public function toDomain(): array
    {
        return [
            'player_id'   => $this->getUser()->id,
            'giveaway_id' => $this->giveaway->id,
            'feast_id'    => $this->giveaway->feast_id,
            'amount'      => $this->amount,

            'player' => [
                'id' => $this->getUser()->id,
            ],
        ];
    }
}
