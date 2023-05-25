<?php

namespace common\modules\tracking\components;

use common\models\Pixel;
use common\modules\tracking\events\interfaces\DispatchedEventInterface;
use common\modules\tracking\observers\FacebookPixelObserver;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

class EventTrackerComponent extends Component
{
    private const EVENT_SETTING_CACHE_TTL = 30; // sec

    public array $observers = [];
    private array $observerInstances = [];

    public function init(): void
    {
        parent::init();

        $this->observers = ArrayHelper::merge($this->observers, $this->loadPixels());
        $this->initObservers();
    }

    public function emitEvent(DispatchedEventInterface $event): void
    {
        foreach ($this->observerInstances as $observer) {
            $observer->handle($event);
        }
    }

    private function loadPixels(): array
    {
        $config = [];

        foreach (Pixel::findAll(['is_enabled' => true]) as $pixel) {
            $config[] = [
                'class'    => FacebookPixelObserver::class,
                'pixelId'  => $pixel->pixel_id,
                'token'    => $pixel->access_token,
                'currency' => 'USD',
            ];
        }

        return $config;
    }

    private function initObservers(): void
    {
        foreach ($this->observers as $config) {
            try {
                $this->observerInstances[] = Yii::createObject($config);
            } catch (InvalidConfigException $e) {
                Yii::error($e);
            }
        }
    }
}
