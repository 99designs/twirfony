<?php

namespace Twirp\TwirpBundle\DependencyInjection\Twirp;

class ServiceMethod
{
    private $twirpPath;
    private $phpMethod;
    private $inputType;

    public function __construct($twirpPath, $phpMethod, $inputType)
    {
        $this->twirpPath = $twirpPath;
        $this->phpMethod = $phpMethod;
        $this->inputType = $inputType;
    }

    public function getTwirpPath()
    {
        return $this->twirpPath;
    }

    public function getPhpMethod()
    {
        return $this->phpMethod;
    }

    public function getInputType()
    {
        return $this->inputType;
    }
}
