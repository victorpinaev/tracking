<?php

namespace common\modules\tracking\events\interfaces;

interface GoogleAnalyticsEventInterface extends DispatchedEventInterface
{
    /**
     * @return array
     */
    public function toGoogleAnalytics(): array;
}
