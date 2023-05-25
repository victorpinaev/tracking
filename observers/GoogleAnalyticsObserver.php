<?php

namespace common\modules\tracking\observers;

use common\modules\tracking\events\interfaces\DispatchedEventInterface;
use common\modules\tracking\events\interfaces\GoogleAnalyticsEventInterface;
use common\modules\tracking\jobs\DuplicateRequestOwoxJob;
use common\modules\tracking\jobs\GoogleAnalyticsJob;
use common\modules\tracking\observers\interfaces\EventObserverInterface;
use domain\user\models\User;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

class GoogleAnalyticsObserver extends BaseObject implements EventObserverInterface
{
    private const DUMMY_CLIENT_ID = 555;

    public string $currency;
    public string $trackerId;
    public bool $sendDuplicate = false;
    public User|null $user = null;

    public function __construct($config = [])
    {
        parent::__construct($config);

        if (!$this->trackerId) {
            throw new InvalidConfigException('Tracker ID required.');
        }

        if (!$this->currency) {
            throw new InvalidConfigException('Currency code  required.');
        }
    }

    /**
     * @param string      $event
     * @param object|null $value
     */
    public function handle(DispatchedEventInterface $event): void
    {
        if (!($event instanceof GoogleAnalyticsEventInterface)) {
            return;
        }

        $this->user = $event->user;

        $data = $this->prepareData($event);
        $options = $this->prepareOptions();

        $primaryJob = new GoogleAnalyticsJob([
            'data'    => $data,
            'options' => $options,
        ]);

        Yii::$app->queueAnalytics->push($primaryJob);

        if ($this->sendDuplicate) {
            $duplicateJob = new DuplicateRequestOwoxJob([
                'trackerId' => $this->trackerId,
                'data'      => $data,
                'options'   => $options,
            ]);

            Yii::$app->queueAnalytics->push($duplicateJob);
        }
    }

    /**
     * @return array
     */
    protected function prepareOptions(): array
    {
        $options = [];

        $tracking = $this->user->profileTracking;
        $userAgent = $tracking->browserUserAgent;

        if ($userAgent) {
            $options['userAgent'] = $userAgent->name;
        }

        return $options;
    }

    /**
     * @param GoogleAnalyticsEventInterface $event
     *
     * @return array
     */
    protected function prepareData(GoogleAnalyticsEventInterface $event): array
    {
        $baseData = $this->composeBaseData();
        $eventData = $event->toGoogleAnalytics();

        return \array_merge($baseData, $eventData);
    }

    /**
     * @param \domain\user\models\User $user
     *
     * @return array
     */
    protected function composeBaseData(): array
    {
        $tracking = $this->user->profileTracking;

        $cId = $tracking->ga_cid ?: self::DUMMY_CLIENT_ID;

        $data = [
            'v'   => 1,
            'tid' => $this->trackerId,
            'cu'  => $this->currency,
            'ni'  => 1,
            't'   => 'event',
            // Below is dynamic data
            'cid' => $cId,      // значение полученное на шаге II.1, если cid пустой отправляем 555
            'cd2' => $this->user->id, // ID пользователя в вашей базе данных
            'cd3' => $cId,      // значение полученное на шаге II.1, если cid пустой отправляем 555
            'uid' => $this->user->id, // ID пользователя в вашей базе данных
            'z'   => \mt_rand(),   // это блокировка для кеширования, просто уникальное число
        ];

        if ($tracking->ip) {
            $data['uip'] = $tracking->ip; // IP пользователя (если данных о ip нет, просто не передаем этот параметр вообще)
        }

        return $data;
    }
}
