<?php

namespace common\modules\tracking\events;

use common\helpers\MoneyHelper;
use common\models\Leaderboard;
use common\models\LeaderboardUser;
use common\models\Prize;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use domain\feast\services\FeastProgressService;
use domain\user\models\User;
use yii\base\BaseObject;

class LeaderboardPrizeTakenEvent extends BaseObject implements AmplitudeEventInterface
{
    public LeaderboardUser $leaderboardUser;
    public Prize $prize;

    public function getUser(): User
    {
        return $this->leaderboardUser->user;
    }

    public function getLeaderboard(): Leaderboard
    {
        return $this->leaderboardUser->leaderboard;
    }

    public function getInternalName(): string
    {
        return EventEnum::LEADERBOARD_PRIZE_TAKEN;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'prize_price'          => MoneyHelper::convertFromCents($this->getPrizePrice()),
            'prize_type'           => $this->prize->prize_type,
            'leaderboard_id'       => $this->leaderboard->id,
            'leaderboard_title'    => $this->leaderboard->title,
            'feast_slug'           => $this->leaderboard->feast->slug,
            'current_user_level'   => FeastProgressService::getAbsoluteUserLevel($this->user, $this->leaderboard->feast),
            'tier_name'            => MoneyHelper::format($this->leaderboard->min_deposit) . '-' . MoneyHelper::format($this->leaderboard->max_deposit),
            'leaderboard_position' => $this->leaderboardUser->position,
            'prize_slug'           => $this->getPrizeSlug(),
        ];

        return [
            'event_type'       => EventEnum::LEADERBOARD_PRIZE_TAKEN,
            'event_properties' => $eventProperties,
        ];
    }

    private function getPrizePrice(): int
    {
        if ($this->prize->item_id) {
            return $this->prize->item->depicting_price;
        }

        if ($this->prize->case_id) {
            return $this->prize->case->price;
        }

        if ($this->prize->money_pack_id) {
            return $this->prize->moneyPack->amount;
        }

        return 0;
    }

    private function getPrizeSlug(): string
    {
        if ($this->prize->item_id) {
            return $this->prize->item->market_hash_name;
        }

        if ($this->prize->case_id) {
            return $this->prize->case->name;
        }

        if ($this->prize->money_pack_id) {
            return $this->prize->moneyPack->title;
        }

        return '-';
    }
}
