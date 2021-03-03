#!/bin/sh

set -eu

echo
echo "\n\n\nInstalling dependencies"
sudo apt-get install -y protobuf-compiler

go install ./protoc-gen-twirp_php

export PATH=$PATH:$HOME/go/bin

echo
echo "Rebuilding generated code"
cd ./example/Haberdasher
rm -rf src/AppBundle/Twirp src/GPBMetadata
protoc --twirp_php_out src --php_out src haberdasher.proto