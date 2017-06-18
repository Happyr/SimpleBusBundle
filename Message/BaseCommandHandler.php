<?php

namespace Happyr\SimpleBusBundle\Message;

use Doctrine\ORM\EntityManagerInterface;
use SimpleBus\Message\Recorder\RecordsMessages;

/**
 * @author Tobias Nyholm
 */
abstract class BaseCommandHandler
{
    /**
     * @var EntityManagerInterface em
     */
    protected $em;

    /**
     * @var RecordsMessages eventRecorder
     */
    protected $eventRecorder;

    /**
     * @param RecordsMessages $eventRecorder
     */
    public function setEventRecorder(RecordsMessages $eventRecorder)
    {
        $this->eventRecorder = $eventRecorder;
    }

    /**
     * @param EntityManagerInterface $em
     */
    public function setEntityManager(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
}
