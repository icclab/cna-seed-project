#!/bin/bash

TAG=${DOCKER_IMAGE_TAG:-"logging-dev"}
echo "Using tag ${TAG}"
docker build -t="icclabcna/zurmo_logstash:${TAG}" .
