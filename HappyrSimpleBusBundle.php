<?php

namespace Happyr\SimpleBusBundle;

use Happyr\SimpleBusBundle\DependencyInjection\CompilerPass\CompilerPasses;
use Happyr\SimpleBusBundle\DependencyInjection\HappyrSimpleBusExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HappyrSimpleBusBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CompilerPasses());
    }

    public function getContainerExtension()
    {
        return new HappyrSimpleBusExtension();
    }

}
