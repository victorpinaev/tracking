<?php

namespace common\modules\tracking\jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class TapfiliateRegistrationJob extends BaseObject implements JobInterface
{
    public array $data;

    public function execute($queue): void
    {
        Yii::$app->tapfiliate->createCustomer($this->data);
    }
}
