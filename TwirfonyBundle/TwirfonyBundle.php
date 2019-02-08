<?php

namespace Twirfony\TwirfonyBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Twirfony\TwirfonyBundle\DependencyInjection\Compiler\TwirpServicePass;

class TwirfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TwirpServicePass());
    }
}
