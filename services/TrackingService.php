<?php

namespace common\modules\tracking\services;

use common\models\TrackingSession;
use common\modules\tracking\enums\EventEnum;
use Yii;
use yii\base\InvalidArgumentException;

class TrackingService
{
    public static function instance(): self
    {
        return Yii::$container->get(self::class);
    }

    public function saveAmpSession(string $eventName, string $sessionId, string ...$keys): TrackingSession
    {
        if (!\in_array($eventName, EventEnum::initialNames(), true)) {
            throw new InvalidArgumentException('Event "' . $eventName . '" is not a starting.');
        }

        $hash = $this->createHashId([$eventName] + $keys);

        return TrackingSession::safeUpdateOrCreate(['id' => $hash], ['amp_session_id' => $sessionId]);
    }

    public function findAndRemoveAmpSession(string $eventName, string ...$keys): ?TrackingSession
    {
        if (!\in_array($eventName, EventEnum::initialNames(), true)) {
            throw new InvalidArgumentException('Event "' . $eventName . '" is not a starting.');
        }

        $hash = $this->createHashId([$eventName] + $keys);
        $result = TrackingSession::findOne($hash);

        if ($result) {
            $result->delete();
        }

        return $result;
    }

    private function createHashId(array $arguments): string
    {
        return \sha1(\implode('-', $arguments));
    }
}
