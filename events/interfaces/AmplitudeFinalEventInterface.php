<?php

namespace common\modules\tracking\events\interfaces;

use common\models\TrackingSession;

interface AmplitudeFinalEventInterface extends AmplitudeEventInterface
{
    public function restoreAmpSession(string $initialName): ?TrackingSession;
}
