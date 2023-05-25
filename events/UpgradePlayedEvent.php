<?php

namespace common\modules\tracking\events;

use api\models\UserDrop;
use common\enums\SocketMessageEnum;
use common\helpers\ActiveRecordHelper;
use common\helpers\MoneyHelper;
use common\models\upgrade\Upgrades;
use common\models\UsersDrop;
use common\modules\tracking\dtos\CentrifugoMessageDTO;
use common\modules\tracking\enums\DomainEventEnum;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use common\modules\tracking\events\interfaces\CentrifugoEventInterface;
use common\modules\tracking\events\interfaces\DomainEventInterface;
use common\modules\tracking\events\interfaces\FacebookPixelEventInterface;
use domain\user\models\User;
use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

class UpgradePlayedEvent extends BaseObject implements
    AmplitudeEventInterface,
    FacebookPixelEventInterface,
    DomainEventInterface,
    CentrifugoEventInterface
{
    public Upgrades $upgrade;

    public function getUser(): User
    {
        return $this->upgrade->user;
    }

    public function getInternalName(): string
    {
        return EventEnum::SKIN_UPGRADED;
    }

    public function getExternalName(): string
    {
        return DomainEventEnum::UPGRADE_PLAYED;
    }

    public function toAmplitude(): array
    {
        $receivedDropPrice = $this->upgrade->is_win
            ? MoneyHelper::convertFromCents($this->upgrade->desired_amount)
            : null;

        $eventProperties = [
            'upgrade_id'            => $this->upgrade->id,
            'sent_drop_ids'         => ArrayHelper::getColumn($this->upgrade->usedDrops, 'id'),
            'sum_drop_price'        => MoneyHelper::convertFromCents($this->upgrade->bet_amount),
            'upgrade_chance'        => $this->upgrade->chance,
            'price_ratio'           => $this->upgrade->price_ratio,
            'received_drop_id'      => $this->upgrade->drop_id,
            'received_drop_price'   => $receivedDropPrice,
            'upgrade_win'           => (bool) $this->upgrade->is_win,
            'target_drop_hash_name' => $this->upgrade->item->market_hash_name,
        ];

        return [
            'event_type'       => 'upgrade_succeded',
            'event_properties' => $eventProperties,
        ];
    }

    public function toFacebookPixel(): array
    {
        return [
            'event_name' => 'UpgradeSkin',
            'amount'     => $this->upgrade->bet_amount,
        ];
    }

    public function toDomain(): array
    {
        return [
            'upgrade' => [
                'chance' => $this->upgrade->chance_int,
                'bet'    => [
                    'price' => $this->upgrade->bet_amount,
                    'drops' => ArrayHelper::toArray($this->upgrade->usedDrops, [UsersDrop::class => ['id', 'price']]),
                ],
                'desired' => [
                    'price' => $this->upgrade->desired_amount,
                    'drops' => $this->upgrade->is_win
                        ? [['id' => $this->upgrade->drop->id, 'price' => $this->upgrade->drop->price]]
                        : null,
                    'items' => [['id' => $this->upgrade->item_id, 'price' => $this->upgrade->desired_amount]],
                ],
                'is_win' => $this->upgrade->is_win,
            ],
            'player' => [
                'id'            => $this->user->id,
                'roles'         => $this->user->roleNames,
                'feature_flags' => $this->getLaunchDarklyFlags(),
            ],
        ];
    }

    public function toCentrifugo(): array
    {
        $messages = [];

        foreach ($this->upgrade->usedDrops as $usedDrop) {
            $usedDrop = ActiveRecordHelper::castTo(UserDrop::class, $usedDrop);

            $messages[] = new CentrifugoMessageDTO(config: [
                'name'   => SocketMessageEnum::DROP_UPDATE,
                'data'   => $usedDrop->toArray([], UserDrop::DEFAULT_EXPAND),
                'params' => ['user_id' => $usedDrop->user_id],
            ]);
        }

        $messages[] = new CentrifugoMessageDTO([
            'name'   => SocketMessageEnum::SKIN_BALANCE_UPDATE,
            'data'   => ['amount' => \frontend\models\UsersDrop::pendingPrice($this->upgrade->user_id)],
            'params' => ['user_id' => $this->upgrade->user_id],
        ]);

        return $messages;
    }

    private function getLaunchDarklyFlags(): array
    {
        // If we are outside frontend app (launchDarkly component unavailable).
        if (!Yii::$app->has('launchDarkly') || !Yii::$app->launchDarkly->isOperable()) {
            return [];
        }

        return Yii::$app->launchDarkly->allFlagsState()->toValuesMap();
    }
}
