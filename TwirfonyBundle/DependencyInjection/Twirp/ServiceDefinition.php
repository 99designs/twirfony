<?php

namespace Twirfony\TwirfonyBundle\DependencyInjection\Twirp;

class ServiceDefinition
{
    private $serviceId;

    /**
     * @var ServiceMethod[]
     */
    private $methods = [];

    public function __construct($serviceId)
    {
        $this->serviceId = $serviceId;
    }

    public function addMethod(ServiceMethod $method)
    {
        $this->methods[] = $method;
    }

    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @return ServiceMethod[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }
}
