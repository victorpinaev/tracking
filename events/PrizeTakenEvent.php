<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\feast\models\FeastsOld;
use domain\feast\models\FeastsProgressPrizesOld;
use domain\feast\services\FeastProgressService;
use domain\user\models\User;
use yii\base\BaseObject;

class PrizeTakenEvent extends BaseObject implements AmplitudeEventInterface
{
    public FeastsOld $feast;
    public FeastsProgressPrizesOld $prize;

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
        return EventEnum::PRIZE_TAKEN;
    }

    public function toAmplitude(): array
    {
        $currentUserLevel = FeastProgressService::getAbsoluteUserLevel($this->user, $this->feast);
        $prizeType = $this->prize->feastsShopItem->entity_type ?? null;
        $prizePrice = $this->getPrizePrice();

        return [
            'event_type'       => EventEnum::PRIZE_TAKEN,
            'event_properties' => [
                'feast_slug'         => $this->feast->slug,
                'current_user_level' => $currentUserLevel,
                'progress_level'     => $this->prize->level,
                'prize_type'         => $prizeType,
                'prize_price'        => MoneyHelper::convertFromCents($prizePrice),
            ],
        ];
    }

    private function getPrizePrice(): ?int
    {
        $feastsShopItem = $this->prize->feastsShopItem;

        if (!$feastsShopItem) {
            return null;
        }

        $source = $feastsShopItem->getSource();

        if (!$source) {
            return null;
        }

        return $source->getMoneyPrice();
    }
}
