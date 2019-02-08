<?php

namespace Twirfony;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

// Allows for easy testing of api clients that use guzzle.
class GuzzleTestCase extends KernelTestCase
{
    public static function getClient(string $baseurl): Client
    {
        $stack = new HandlerStack();
        $stack->setHandler(function(RequestInterface $psrRequest, $options) {
            $kernel = static::bootKernel();

            $symfonyRequest = Request::create(
                $psrRequest->getUri(),
                $psrRequest->getMethod(),
                [], [], [], [],
                $psrRequest->getBody()->getContents()
            );
            $symfonyRequest->headers = new HeaderBag($psrRequest->getHeaders());

            $symfonyResponse = $kernel->handle($symfonyRequest, HttpKernelInterface::MASTER_REQUEST, false);

            $guzzleResponse = new Response(
                $symfonyResponse->getStatusCode(),
                $symfonyResponse->headers->all(),
                $symfonyResponse->getContent(),
                $symfonyResponse->getProtocolVersion()
            );

            return new FulfilledPromise($guzzleResponse);
        });

        return new Client([
            'handler' => $stack,
            'base_uri' => $baseurl,
        ]);
    }
}
