<?php

namespace common\modules\tracking\observers\interfaces;

use common\modules\tracking\events\interfaces\DispatchedEventInterface;

interface EventObserverInterface
{
    public function handle(DispatchedEventInterface $event): void;
}
