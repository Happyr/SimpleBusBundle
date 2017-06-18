<?php

namespace Happyr\SimpleBusBundle;

use Happyr\Mq2phpBundle\DependencyInjection\Compiler\RegisterConsumers;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HappyrSimpleBusBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterConsumers());
    }
}
