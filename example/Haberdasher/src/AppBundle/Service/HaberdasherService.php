<?php

namespace AppBundle\Service;

use AppBundle\Twirp\HaberdasherInterface;
use AppBundle\Twirp\Hat;
use AppBundle\Twirp\Size;
use Twirfony\TwirpService;

class HaberdasherService implements TwirpService, HaberdasherInterface
{
    public function makeHat(Size $size): Hat
    {
        return (new Hat)
            ->setInches($size->getInches())
            ->setColor("blue")
            ->setName("Fedora");
    }
}
