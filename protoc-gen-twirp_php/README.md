# Twirp PHP generator

## Installation

```bash
go get -u github.com/99designs/protoc-gen-twirp_php
```

You will also need:
 - [protoc](https://github.com/golang/protobuf), the protobuf compiler. You need
   version 3+.
 - [github.com/golang/protobuf/protoc-gen-go](https://github.com/golang/protobuf/),
   the Go protobuf generator plugin. Get this with `go get`.
   

## Generating PHP

The twirp [Haberdasher example](https://github.com/twitchtv/twirp/wiki/Usage-Example:-Haberdasher) 
can be found in `example/`:

```bash
cd example
protoc --twirp_php_out . --php_out . haberdasher.proto
```

## Using the client

The generated client uses [Guzzle v6](http://docs.guzzlephp.org/) for making requests. 

A dependency is not ideal, but the PHP standard library is lacking in this department.

```php

$client = new GuzzleHttp\Client([
    'base_uri' => $myHost . '/twirp/' // supports soon to be released twirp v6
]);

$haberdasher = new Twirp\Example\Haberdasher\HaberdasherClient($client);
$haberdasher->makeHat(...)
```
