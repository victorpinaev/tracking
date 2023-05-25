<?php

namespace common\modules\tracking\observers;

use common\modules\producer\services\ProducerService;
use common\modules\tracking\events\interfaces\DispatchedEventInterface;
use common\modules\tracking\events\interfaces\DomainEventInterface;
use common\modules\tracking\observers\interfaces\EventObserverInterface;
use yii\base\BaseObject;

class DomainObserver extends BaseObject implements EventObserverInterface
{
    private ProducerService $producerService;

    public function __construct(ProducerService $producerService, $config = [])
    {
        $this->producerService = $producerService;
        parent::__construct($config);
    }

    public function handle(DispatchedEventInterface $event): void
    {
        if (!($event instanceof DomainEventInterface)) {
            return;
        }

        $this->producerService->emit($event->getExternalName(), $event->toDomain());
    }
}
