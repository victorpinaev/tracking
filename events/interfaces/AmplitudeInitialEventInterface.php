<?php

namespace common\modules\tracking\events\interfaces;

interface AmplitudeInitialEventInterface extends AmplitudeEventInterface
{
    public function saveAmpSession(string $sessionId): void;
}
