<?php

namespace Twirp\TwirpBundle\DependencyInjection\Twirp;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ServiceRegistry
{
    /**
     * @var ServiceDefinition[] $definitions
     */
    private $definitions = [];

    public function addService(ServiceDefinition $definition)
    {
        $this->definitions[] = $definition;
    }

    public function loadRoutes()
    {
        $routes = new RouteCollection();
        foreach ($this->definitions as $definition) {
            foreach ($definition->getMethods() as $method) {
                $route = new Route($method->getTwirpPath(), [
                    'inputType' => $method->getInputType(),
                    'service' => $definition->getServiceId(),
                    'method' => $method->getPhpMethod(),
                    '_controller' => 'TwirpBundle:Twirp:rpc'
                ]);
                $routes->add('twirp_' . $method->getTwirpPath(), $route);
            }
        }
        return $routes;
    }
}
