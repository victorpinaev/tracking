<?php

namespace common\modules\tracking\observers;

use Cake\Chronos\Chronos;
use common\enums\AppEnum;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use common\modules\tracking\events\interfaces\AmplitudeFinalEventInterface;
use common\modules\tracking\events\interfaces\AmplitudeInitialEventInterface;
use common\modules\tracking\events\interfaces\DispatchedEventInterface;
use common\modules\tracking\jobs\AmplitudeJob;
use common\modules\tracking\observers\interfaces\EventObserverInterface;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

class AmplitudeObserver extends BaseObject implements EventObserverInterface
{
    private const COOKIE_KEY = 'amp_session_id';

    /**
     * Amplitude API key.
     *
     * @var string
     */
    public string $apiKey;

    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigException
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        if (!$this->apiKey) {
            throw new InvalidConfigException('API key required.');
        }
    }

    public function handle(DispatchedEventInterface $event): void
    {
        if (!($event instanceof AmplitudeEventInterface)) {
            return;
        }

        $data = $this->prepareData($event);

        if ($event instanceof AmplitudeInitialEventInterface) {
            $event->saveAmpSession($data['events']['session_id']);
        }

        $job = new AmplitudeJob([
            'data' => $data,
        ]);

        Yii::$app->queueAnalytics->push($job);
    }

    private function prepareData(AmplitudeEventInterface $event): array
    {
        $data = [
            'api_key' => $this->apiKey,
            'events'  => $event->toAmplitude(),
            'options' => ['min_id_length' => 1],
        ];

        $timestamp = Chronos::now()->getTimestamp();

        if (\in_array($event->getInternalName(), EventEnum::withoutAmpSession(), true)) {
            $data['events']['session_id'] = -1;
        } else {
            $storedSession = null;

            if ($event instanceof AmplitudeFinalEventInterface) {
                $storedSession = $event->restoreAmpSession(EventEnum::initialName($event->getInternalName()));
            }

            $data['events']['session_id'] = $_COOKIE[self::COOKIE_KEY] ?? $storedSession->amp_session_id ?? $timestamp;
        }

        $data['events']['user_properties']['longterm_level'] = $event->user->level_number;
        $data['events']['user_properties']['user_role'] = \implode(', ', $event->user->roleNames);
        $data['events']['user_id'] = $event->user->id;
        $data['events']['time'] = $timestamp;

        $data['events'] = ArrayHelper::merge($data['events'], [
            'event_properties' => [
                'website' => \defined('MIRROR_MODE') ? AppEnum::siteId(MIRROR_MODE) : null,
            ],
        ]);

        $data['events'] = ArrayHelper::merge($data['events'], $this->composeLaunchDarklyFlags());

        return $data;
    }

    private function composeLaunchDarklyFlags(): array
    {
        // If we are outside frontend app (launchDarkly component unavailable).
        if (!Yii::$app->has('launchDarkly') || !Yii::$app->launchDarkly->isOperable()) {
            return [];
        }

        $all = Yii::$app->launchDarkly->allFlagsState()->toValuesMap();
        $result = [];

        /**
         * Required format ("ld_" - prefix to filter Launch Darkly values):.
         *
         * @see https://morejam.atlassian.net/browse/SC-3789
         *
         *  featureFlags: [
         *      'ld_case_page.add_to_battle_button:false',
         *      'ld_case_page.roulette_content_variations:5'
         *      ...
         *  ]
         */
        foreach ($all as $key => $value) {
            if (\is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            $result[] = 'ld_' . $key . ':' . ((string) $value);
        }

        return [
            'user_properties' => [
                'featureFlags' => $result,
            ],
        ];
    }
}
