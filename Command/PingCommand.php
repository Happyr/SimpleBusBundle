<?php

namespace Happyr\SimpleBusBundle\Command;

use Happyr\SimpleBusBundle\Message\Command\Ping;
use Happyr\SimpleBusBundle\Message\Event\Pong;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Test if SimpleBus works.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class PingCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('happyr:simplebus:ping')
            ->setDescription('Ping test');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->get('command_bus')->handle(new Ping('4711'));
        $this->get('event_bus')->handle(new Pong('4711-pong'));
    }

    /**
     * @param string $service
     *
     * @return object
     */
    private function get(string $service)
    {
        return $this->getContainer()->get($service);
    }
}
