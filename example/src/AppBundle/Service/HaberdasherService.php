<?php

namespace AppBundle\Service;

use AppBundle\Twirp\HaberdasherInterface;
use AppBundle\Twirp\Hat;
use AppBundle\Twirp\Size;
use Twirp\TwirpService;

class HaberdasherService implements TwirpService, HaberdasherInterface
{
    public function makeHat(Size $size): Hat
    {
        // TODO: Implement makeHat() method.
    }
}
