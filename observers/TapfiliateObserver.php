<?php

namespace common\modules\tracking\observers;

use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\DispatchedEventInterface;
use common\modules\tracking\events\interfaces\TapfiliateEventInterface;
use common\modules\tracking\jobs\TapfiliatePaymentSuccededJob;
use common\modules\tracking\jobs\TapfiliateRegistrationJob;
use common\modules\tracking\observers\interfaces\EventObserverInterface;
use Yii;
use yii\base\BaseObject;
use yii\base\Exception;

class TapfiliateObserver extends BaseObject implements EventObserverInterface
{
    public function handle(DispatchedEventInterface $event): void
    {
        if (!$event instanceof TapfiliateEventInterface) {
            return;
        }

        if (!$event->getUser()->profileTracking?->tf_click_id) {
            return; // Do nothing if user has been registered without referral code.
        }

        $data = $this->prepareData($event);

        $job = match ($event->getInternalName()) {
            EventEnum::ACCOUNT_CREATED => new TapfiliateRegistrationJob(['data' => $data]),
            EventEnum::ACCOUNT_FUNDED  => new TapfiliatePaymentSuccededJob(['data' => $data]),
            default                    => throw new Exception('Unprocessable event "' . $event->getInternalName() . '".'),
        };

        Yii::$app->queueAnalytics->push($job);
    }

    private function prepareData(TapfiliateEventInterface $event): array
    {
        $data = $event->toTapfiliate();

        $data['click_id'] = $event->getUser()->profileTracking?->tf_click_id;
        $data['user_agent'] = $event->getUser()->profileTracking?->browserUserAgent?->name;
        $data['ip'] = $event->getUser()->profileTracking?->ip;
        $data['meta_data'] = $event->getUser()->profileTracking?->tf_meta_data;

        return $data;
    }
}
