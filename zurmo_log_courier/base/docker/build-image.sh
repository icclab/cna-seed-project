TAG=${DOCKER_IMAGE_TAG:-"latest"}
sudo docker build -t="icclabcna/zurmo_log_courier:${TAG}" .
