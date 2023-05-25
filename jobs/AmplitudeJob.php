<?php

namespace common\modules\tracking\jobs;

use common\modules\tracking\enums\EventEnum;
use Yii;
use yii\base\BaseObject;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\queue\JobInterface;
use yii\queue\Queue;

class AmplitudeJob extends BaseObject implements JobInterface
{
    private const ENDPOINT = 'https://api2.amplitude.com/batch';
    private const REQUEST_TIMEOUT = 10; // sec

    public $data;

    /**
     * @param Queue $queue
     *
     * @throws \yii\httpclient\Exception
     */
    public function execute($queue): void
    {
        $client = new Client([
            'transport'     => CurlTransport::class,
            'requestConfig' => [
                'format'  => Client::FORMAT_JSON,
                'options' => [
                    'timeout' => self::REQUEST_TIMEOUT,
                ],
            ],
        ]);

        $response = $client->post(self::ENDPOINT, $this->data)->send();

        if (!$response->isOk) {
            Yii::error('Failed to send info to Amplitude. Details: ' . $response->toString());
        }

        $eventName = $this->data['events']['event_type'];

        if (\in_array($eventName, [EventEnum::ACCOUNT_FUNDED, EventEnum::PAYMENT_ERROR], true)) {
            $paymentId = $this->data['events']['event_type']['event_properties']['payment_id']   ?? 'null';
            $externalId = $this->data['events']['event_type']['event_properties']['external_id'] ?? 'null';

            Yii::info(
                \json_encode(
                    [
                        'amplitude_event' => [
                            'name'        => $eventName,
                            'payment_id'  => $paymentId,
                            'external_id' => $externalId,
                        ],
                    ],
                    JSON_THROW_ON_ERROR
                ),
                'custom',
            );
        }
    }
}
