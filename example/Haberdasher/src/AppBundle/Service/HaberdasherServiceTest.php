<?php

namespace AppBundle\Service;

use AppBundle\Twirp\HaberdasherClient;
use AppBundle\Twirp\Size;
use Twirfony\GuzzleTestCase;

class HaberdasherServiceTest extends GuzzleTestCase
{
    public function testIndex()
    {
        $client = static::getClient('http://localhost/twirp/');
        $client = new HaberdasherClient($client);

        $hat = $client->makeHat((new Size)->setInches(2));

        $this->assertEquals('blue', $hat->getColor());
        $this->assertEquals('Fedora', $hat->getName());
        $this->assertEquals(2, $hat->getInches());
    }
}
