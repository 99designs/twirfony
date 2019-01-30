<?php

namespace Twirp\TwirpBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Twirp\TwirpBundle\DependencyInjection\Compiler\TwirpServicePass;

class TwirpBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TwirpServicePass());
    }
}
