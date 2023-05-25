<?php

namespace common\modules\tracking\events;

use common\enums\SocketMessageEnum;
use common\helpers\MoneyHelper;
use common\models\TrackingSession;
use common\modules\tracking\dtos\CentrifugoMessageDTO;
use common\modules\tracking\enums\DomainEventEnum;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeFinalEventInterface;
use common\modules\tracking\events\interfaces\CentrifugoEventInterface;
use common\modules\tracking\events\interfaces\DomainEventInterface;
use common\modules\tracking\services\TrackingService;
use domain\battle\models\Battle;
use domain\feast\models\FeastsTransactionsOld;
use domain\user\models\User;
use yii\base\BaseObject;

class BattleFinishedPerUserEvent extends BaseObject implements
    AmplitudeFinalEventInterface,
    DomainEventInterface,
    CentrifugoEventInterface
{
    public Battle $battle;
    private User $user;

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getInternalName(): string
    {
        return EventEnum::BATTLE_FINISHED_PER_USER;
    }

    public function getExternalName(): string
    {
        return DomainEventEnum::BATTLE_PLAYED;
    }

    public function toAmplitude(): array
    {
        $winSum = $this->hasBeenWon() ? $this->battle->win_sum : 0;

        $eventProperties = [
            'battle_id'          => $this->battle->id,
            'win_sum'            => MoneyHelper::convertFromCents($winSum),
            'participants_count' => \count($this->battle->battleUsers),
            'fixed_price'        => MoneyHelper::convertFromCents($this->battle->fixed_price),
            'point_amount'       => $this->getFeastPoints(),
            'bots_count'         => 0,
        ];

        return [
            'event_type'       => EventEnum::BATTLE_FINISHED_PER_USER,
            'event_properties' => $eventProperties,
        ];
    }

    public function toDomain(): array
    {
        return [
            'player_id'   => $this->getUser()->id,
            'battle_uuid' => $this->battle->uuid,
            'is_win'      => $this->hasBeenWon(),

            'player' => [
                'id' => $this->getUser()->id,
            ],
        ];
    }

    public function restoreAmpSession(string $initialName): ?TrackingSession
    {
        return TrackingService::instance()->findAndRemoveAmpSession($initialName, $this->battle->id, $this->user->id);
    }

    public function toCentrifugo(): array
    {
        if (!$this->hasBeenWon()) {
            return [];
        }

        $messages[] = new CentrifugoMessageDTO([
            'name'   => SocketMessageEnum::SKIN_BALANCE_UPDATE,
            'data'   => ['amount' => \frontend\models\UsersDrop::pendingPrice($this->user->id)],
            'params' => ['user_id' => $this->user->id],
        ]);

        return $messages;
    }

    private function getFeastPoints(): int
    {
        $userStepsIds = $this->battle
            ->getBattleSteps()
            ->select('id')
            ->andWhere(['user_id' => $this->user->id])
            ->column()
        ;

        return (int) FeastsTransactionsOld::find()
            ->andWhere([
                'entity_id' => $userStepsIds,
                'reason'    => FeastsTransactionsOld::REASON_BATTLE_STEP,
            ])
            ->sum('amount')
        ;
    }

    private function hasBeenWon(): bool
    {
        return $this->user->id === $this->battle->winner_id;
    }
}
