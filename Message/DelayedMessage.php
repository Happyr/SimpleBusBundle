<?php

namespace Happyr\SimpleBusBundle\Message;

/**
 * Delay a message for some time.
 */
interface DelayedMessage
{
    /**
     * @return int milliseconds
     */
    public function getDelayedTime();
}
