<?php

namespace common\modules\tracking\observers;

use common\modules\tracking\events\interfaces\CentrifugoEventInterface;
use common\modules\tracking\events\interfaces\DispatchedEventInterface;
use common\modules\tracking\observers\interfaces\EventObserverInterface;
use common\services\EmitterService;
use yii\base\BaseObject;

class CentrifugoObserver extends BaseObject implements EventObserverInterface
{
    public function __construct(
        private readonly EmitterService $emitter,
        $config = []
    ) {
        parent::__construct($config);
    }

    public function handle(DispatchedEventInterface $event): void
    {
        if (!($event instanceof CentrifugoEventInterface)) {
            return;
        }

        foreach ($event->toCentrifugo() as $payload) {
            $this->emitter->message(
                $payload->name,
                $payload->data,
                $payload->params,
                $payload->delay
            );
        }
    }
}
