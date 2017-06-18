<?php

namespace Happyr\SimpleBusBundle\Message;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
abstract class BaseEventSubscriber implements AutoRegisteredEventSubscriber
{
    /**
     * @var EntityManagerInterface em
     */
    protected $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function setEntityManager(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
}
