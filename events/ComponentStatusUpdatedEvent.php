<?php

namespace common\modules\tracking\events;

use common\enums\FeatureEnum;
use common\enums\SocketMessageEnum;
use common\modules\tracking\dtos\CentrifugoMessageDTO;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\CentrifugoEventInterface;
use domain\system\services\FeatureService;
use domain\user\models\User;
use Yii;
use yii\base\BaseObject;

class ComponentStatusUpdatedEvent extends BaseObject implements CentrifugoEventInterface
{
    public FeatureEnum $feature;

    public function getUser(): User
    {
        $fakeUser = new User();
        $fakeUser->id = 101; // this is fake id for internal usage

        return $fakeUser;
    }

    public function getInternalName(): string
    {
        return EventEnum::COMPONENT_STATUS_UPDATE;
    }

    public function toCentrifugo(): array
    {
        $featureService = Yii::$container->get(FeatureService::class);

        $messages = [];

        $messages[] = new CentrifugoMessageDTO([
            'name' => SocketMessageEnum::COMPONENT_STATUS_UPDATE,
            'data' => [
                'component' => $this->feature->value,
                'status'    => (int) $featureService->isActive($this->feature), // 0 if disabled, 1 if enabled
            ],
        ]);

        return $messages;
    }
}
