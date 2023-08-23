<?php

namespace Twirfony\TwirfonyBundle\Event;

use Google\Protobuf\Internal\Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class ErrorEvent extends Event
{
    const NAME = 'twirp.rpc_error';

    private $request;
    private $serviceId;
    private $method;
    private $input;
    private $exception;

    public function __construct(Request $request, $serviceId, $method, Message $input = null, \Throwable $exception = null)
    {
        $this->request = $request;
        $this->serviceId = $serviceId;
        $this->method = $method;
        $this->input = $input;
        $this->exception = $exception;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getServiceId()
    {
        return $this->serviceId;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getInput(): Message
    {
        return $this->input;
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }
}
