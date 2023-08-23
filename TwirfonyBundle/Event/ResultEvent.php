<?php

namespace Twirfony\TwirfonyBundle\Event;

use Google\Protobuf\Internal\Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class ResultEvent extends Event
{
    const NAME = 'twirp.rpc_result';

    private $request;
    private $serviceId;
    private $method;
    private $input;
    private $output;

    public function __construct(Request $request, $serviceId, $method, Message $input, Message $output)
    {
        $this->request = $request;
        $this->serviceId = $serviceId;
        $this->method = $method;
        $this->input = $input;
        $this->output = $output;
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

    public function getOutput(): Message
    {
        return $this->output;
    }
}
