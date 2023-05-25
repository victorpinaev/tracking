<?php

namespace common\modules\tracking\events\interfaces;

interface TapfiliateEventInterface extends DispatchedEventInterface
{
    public function toTapfiliate(): array;
}
