<?php

namespace common\modules\tracking\events\interfaces;

interface AmplitudeEventInterface extends DispatchedEventInterface
{
    public function toAmplitude(): array;
}
