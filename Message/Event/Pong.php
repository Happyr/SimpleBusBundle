<?php

namespace Happyr\SimpleBusBundle\Message\Event;

use JMS\Serializer\Annotation as Serializer;
use Happyr\SimpleBusBundle\Message\DelayedMessage;

class Pong implements DelayedMessage
{
    /**
     * @var mixed
     * @Serializer\Type("string")
     */
    private $data;

    /**
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public function getDelayedTime()
    {
        return 5000;
    }
}
