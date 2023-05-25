<?php

namespace common\modules\tracking\events;

use api\models\UserDrop;
use common\enums\SocketMessageEnum;
use common\helpers\ActiveRecordHelper;
use common\helpers\MoneyHelper;
use common\modules\tracking\dtos\CentrifugoMessageDTO;
use common\modules\tracking\enums\DomainEventEnum;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use common\modules\tracking\events\interfaces\CentrifugoEventInterface;
use common\modules\tracking\events\interfaces\DomainEventInterface;
use domain\user\models\User;
use frontend\models\UsersDrop;
use JetBrains\PhpStorm\ArrayShape;
use Yii;
use yii\base\BaseObject;

class SkinSoldEvent extends BaseObject implements
    AmplitudeEventInterface,
    DomainEventInterface,
    CentrifugoEventInterface
{
    public UsersDrop $drop;

    public function getUser(): User
    {
        return $this->drop->user;
    }

    public function getInternalName(): string
    {
        return EventEnum::SKIN_SOLD;
    }

    public function getExternalName(): string
    {
        return DomainEventEnum::DROP_SOLD;
    }

    #[ArrayShape(['event_type' => 'string', 'event_properties' => 'array'])]
    public function toAmplitude(): array
    {
        $eventProperties = [
            'drop_id'          => $this->drop->id,
            'market_hash_name' => $this->drop->item->market_hash_name,
            'drop_price'       => MoneyHelper::convertFromCents($this->drop->price),
        ];

        return [
            'event_type'       => 'skin_sold',
            'event_properties' => $eventProperties,
        ];
    }

    #[ArrayShape(['drop' => 'array', 'player' => 'array'])]
    public function toDomain(): array
    {
        return [
            'drop' => [
                'id'    => $this->drop->id,
                'price' => $this->drop->price,
            ],
            'player' => [
                'id'            => $this->drop->user->id,
                'roles'         => $this->drop->user->roleNames,
                'feature_flags' => $this->getLaunchDarklyFlags(),
            ],
        ];
    }

    public function toCentrifugo(): array
    {
        $drop = ActiveRecordHelper::castTo(UserDrop::class, $this->drop);

        $messages[] = new CentrifugoMessageDTO([
            'name'   => SocketMessageEnum::DROP_UPDATE,
            'data'   => $drop->toArray([], UserDrop::DEFAULT_EXPAND),
            'params' => ['user_id' => $this->drop->user_id],
        ]);

        $messages[] = new CentrifugoMessageDTO([
            'name'   => SocketMessageEnum::SKIN_BALANCE_UPDATE,
            'data'   => ['amount' => \frontend\models\UsersDrop::pendingPrice($this->drop->user_id)],
            'params' => ['user_id' => $this->drop->user_id],
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
