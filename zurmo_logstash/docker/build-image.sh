#!/bin/bash

TAG=${DOCKER_IMAGE_TAG:-"master"}
echo "Using tag ${TAG}"
docker build -t="icclabcna/zurmo_logstash:${TAG}" .
