<?php

namespace AppBundle\Service;

use AppBundle\Twirp\EmptyRequest;
use AppBundle\Twirp\HaberdasherClient;
use AppBundle\Twirp\HaberdasherException;
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

    public function testException()
    {
        $this->expectException(HaberdasherException::class);
        $this->expectExceptionMessage('');

        $client = static::getClient('http://localhost/twirp/');
        $client = new HaberdasherClient($client);

        $client->makeHat((new Size)->setInches(-1));
    }

    public function testTwirpError()
    {
        $this->expectException(HaberdasherException::class);
        $this->expectExceptionMessage('size too large');

        $client = static::getClient('http://localhost/twirp/');
        $client = new HaberdasherClient($client);

        $client->makeHat((new Size)->setInches(101));
    }
}
