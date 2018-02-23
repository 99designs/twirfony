#! /usr/local/bin/php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Twirp\Clientcompat\ClientCompatMessage;
use Twirp\Clientcompat\ClientCompatMessage_CompatServiceMethod;
use Twirp\Clientcompat\CompatServiceClient;

$message = new ClientCompatMessage();
try {
    $message->mergeFromString(file_get_contents('php://stdin'));
} catch (\Google\Protobuf\Internal\Exception $e) {
    exit(1);
}

$client = new GuzzleHttp\Client([
    'base_uri' => $message->getServiceAddress().'/twirp/'
]);

$twirp = new \Twirp\Clientcompat\CompatServiceClient($client);

if ($message->getMethod() == ClientCompatMessage_CompatServiceMethod::NOOP) {

    try {
        doNoop($twirp, $message->getRequest());
    } catch (Exception $e) {
        exit(1);
    }
}

if ($message->getMethod() == ClientCompatMessage_CompatServiceMethod::METHOD) {

    try {
        doMethod($twirp, $message->getRequest());
    } catch (Exception $e) {
        exit(1);
    }
}

function doNoop(CompatServiceClient $client, $in)
{
    $req = new \Twirp\Clientcompat\PBEmpty();
    $req->mergeFromString($in);

    try {
        $resp = $client->noopMethod($req);
        echo $resp->serializeToString();
    } catch (\Twirp\Clientcompat\CompatServiceException $e) {
        err($e->getTwirpCode());
    }
}

function doMethod(CompatServiceClient $client, $in)
{
    $req = new \Twirp\Clientcompat\Req();
    $req->mergeFromString($in);

    try {
        $resp = $client->method($req);
        echo $resp->serializeToString();

    } catch (\Twirp\Clientcompat\CompatServiceException $e) {
        err($e->getTwirpCode());
    }
}

function err($message)
{
    file_put_contents('php://stderr', $message);
}
