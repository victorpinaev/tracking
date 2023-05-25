<?php

namespace common\modules\tracking\observers;

use Cake\Chronos\Chronos;
use common\helpers\MoneyHelper;
use common\modules\tracking\events\interfaces\DispatchedEventInterface;
use common\modules\tracking\events\interfaces\FacebookPixelEventInterface;
use common\modules\tracking\jobs\FacebookPixelJob;
use common\modules\tracking\observers\interfaces\EventObserverInterface;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

class FacebookPixelObserver extends BaseObject implements EventObserverInterface
{
    public string $currency;
    public string $pixelId;
    public string $token;

    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigException
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        if (!$this->pixelId) {
            throw new InvalidConfigException('Pixel ID required.');
        }

        if (!$this->token) {
            throw new InvalidConfigException('Token required.');
        }

        if (!$this->currency) {
            throw new InvalidConfigException('Currency code required.');
        }
    }

    public function handle(DispatchedEventInterface $event): void
    {
        if (!($event instanceof FacebookPixelEventInterface)) {
            return;
        }

        $data = $this->prepareData($event);

        $job = new FacebookPixelJob([
            'pixelId' => $this->pixelId,
            'token'   => $this->token,
            'data'    => $data,
        ]);

        Yii::$app->queueAnalytics->push($job);
    }

    /**
     * @param FacebookPixelEventInterface $event
     *
     * @return array
     */
    private function prepareData(FacebookPixelEventInterface $event): array
    {
        $user = $event->user;
        $tracking = $user->profileTracking;

        $eventData = $event->toFacebookPixel();

        $userData = [
            'external_id'       => \hash('md4', $user->id), // hashing needs to be fast, security not required
            'client_ip_address' => $tracking->ip                     ?? null,
            'client_user_agent' => $tracking->browserUserAgent->name ?? null,
            'fbp'               => $tracking->fbp                    ?? null,
            'fbc'               => $tracking->fbc                    ?? null,
        ];

        $data = [
            'event_name'  => $eventData['event_name'],
            'event_time'  => Chronos::now()->getTimestamp(),
            'user_data'   => $this->unsetEmpty($userData),
            'custom_data' => [
                'value'    => MoneyHelper::convertFromCents($eventData['amount']),
                'currency' => $this->currency,
            ],
        ];

        return [
            'data' => [
                0 => $this->unsetEmpty($data),
            ],
        ];
    }

    private function unsetEmpty(array $data): array
    {
        foreach ($data as $key => $val) {
            if (empty($val)) {
                unset($data[$key]);
            }
        }

        return $data;
    }
}
