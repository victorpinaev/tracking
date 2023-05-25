<?php

namespace common\modules\tracking\events\interfaces;

interface DomainEventInterface extends DispatchedEventInterface
{
    public function getExternalName(): string;

    public function toDomain(): array;
}
