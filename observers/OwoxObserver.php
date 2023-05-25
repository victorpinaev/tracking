<?php

namespace common\modules\tracking\observers;

use common\modules\tracking\events\interfaces\DispatchedEventInterface;
use common\modules\tracking\events\interfaces\GoogleAnalyticsEventInterface;
use common\modules\tracking\jobs\OwoxJob;
use Yii;

class OwoxObserver extends GoogleAnalyticsObserver
{
    public function handle(DispatchedEventInterface $event): void
    {
        if (!($event instanceof GoogleAnalyticsEventInterface)) {
            return;
        }

        $this->user = $event->user;

        $data = $this->prepareData($event);
        $options = $this->prepareOptions();

        $job = new OwoxJob([
            'trackerId' => $this->trackerId,
            'data'      => $data,
            'options'   => $options,
        ]);

        Yii::$app->queueAnalytics->push($job);
    }
}
