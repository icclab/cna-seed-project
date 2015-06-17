#!/bin/bash

TAG=${DOCKER_IMAGE_TAG:-"latest"}
docker build -t "icclabcna/elasticdump:${TAG}" .
