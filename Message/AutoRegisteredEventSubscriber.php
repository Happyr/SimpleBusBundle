<?php

namespace Happyr\SimpleBusBundle\Message;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface AutoRegisteredEventSubscriber
{
    /**
     * A namespace + class for the event the current subscriber is listening to.
     *
     * @return string Example: FooEvent:class
     */
    public static function subscribesTo();
}
