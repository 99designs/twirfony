#!/bin/bash

set -euo pipefail

docker run \
	-w /twirfony \
	-v $(pwd):/twirfony \
	golang:1.11-alpine /twirfony/.circleci/test.sh
