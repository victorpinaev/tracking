<?php

namespace common\modules\tracking\jobs;

use Yii;
use yii\base\BaseObject;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\queue\JobInterface;
use yii\queue\Queue;

class GoogleAnalyticsJob extends BaseObject implements JobInterface
{
    private const REQUEST_TIMEOUT = 10; // sec

    public $data;
    public $options;

    /**
     * @param Queue $queue
     *
     * @throws \yii\httpclient\Exception
     */
    public function execute($queue): void
    {
        $url = 'https://www.google-analytics.com/collect';

        $client = new Client([
            'transport'     => CurlTransport::class,
            'requestConfig' => [
                'options' => [
                    'timeout' => self::REQUEST_TIMEOUT,
                ],
            ],
        ]);

        $request = $client->post($url, $this->data, [], $this->options)->setFormat(Client::FORMAT_RAW_URLENCODED);

        $response = $request->send();

        if (!$response->isOk) {
            Yii::error('Failed to send info to GA. Details: ' . $response->toString());
        }
    }
}
