<?php

namespace common\modules\tracking\jobs;

use Yii;
use yii\base\BaseObject;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\queue\JobInterface;
use yii\queue\Queue;

class FacebookPixelJob extends BaseObject implements JobInterface
{
    private const API_VERSION = 'v11.0';
    private const REQUEST_TIMEOUT = 10; // sec

    public $pixelId;
    public $token;
    public $data;

    /**
     * @param Queue $queue
     *
     * @throws \yii\httpclient\Exception
     */
    public function execute($queue): void
    {
        $url = 'https://graph.facebook.com/' . self::API_VERSION . "/{$this->pixelId}/events?access_token={$this->token}";

        $client = new Client([
            'transport'     => CurlTransport::class,
            'requestConfig' => [
                'options' => [
                    'timeout' => self::REQUEST_TIMEOUT,
                ],
            ],
        ]);

        $request = $client->post($url, $this->data);

        $response = $request->send();

        if (!$response->isOk) {
            Yii::error('Failed to send info to FB Pixel. Details: ' . $response->toString());
        }
    }
}
