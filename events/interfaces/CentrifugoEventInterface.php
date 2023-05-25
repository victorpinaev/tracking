<?php

namespace common\modules\tracking\events\interfaces;

use common\modules\tracking\dtos\CentrifugoMessageDTO;

interface CentrifugoEventInterface extends DispatchedEventInterface
{
    /**
     * @return CentrifugoMessageDTO[]
     */
    public function toCentrifugo(): array;
}
