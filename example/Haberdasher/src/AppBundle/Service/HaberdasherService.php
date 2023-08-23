<?php

namespace AppBundle\Service;

use AppBundle\Twirp\HaberdasherInterface;
use AppBundle\Twirp\Hat;
use AppBundle\Twirp\Size;
use Twirfony\TwirpError;
use Twirfony\TwirpService;

class HaberdasherService implements TwirpService, HaberdasherInterface
{
    public function makeHat(Size $size): Hat
    {
        if ($size->getInches() < 0) {
            throw new \Exception();
        }
        if ($size->getInches() > 100) {
            throw new TwirpError(TwirpError::INTERNAL, 'size too large');
        }

        return (new Hat)
            ->setInches($size->getInches())
            ->setColor("blue")
            ->setName("Fedora");
    }
}
