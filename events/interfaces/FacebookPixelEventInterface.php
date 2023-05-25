<?php

namespace common\modules\tracking\events\interfaces;

interface FacebookPixelEventInterface extends DispatchedEventInterface
{
    /**
     * @return array
     */
    public function toFacebookPixel(): array;
}
