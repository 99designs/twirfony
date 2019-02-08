#!/bin/sh

set -eou pipefail

echo
echo "\n\n\nInstalling dependencies"
apk add --no-progress --no-cache \
    protobuf \
    git \
    curl \
    php7 \
    php7-json \
    php7-phar \
    php7-mbstring \
    php7-openssl \
    php7-dom \
    php7-tokenizer \
    php7-xml \
    php7-xmlwriter \
    composer

go install ./protoc-gen-twirp_php
composer --no-ansi install

echo
echo "Rebuilding generated code"
cd /twirfony/example/Haberdasher
rm -rf src/AppBundle/Twirp src/GPBMetadata
protoc --twirp_php_out src --php_out src haberdasher.proto

echo
echo "Running tests"
cd /twirfony
./vendor/bin/phpunit
