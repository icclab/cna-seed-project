TAG=${DOCKER_IMAGE_TAG:-"latest"}
sudo docker build -t="gtoff/zurmo_galera_cluster:${TAG}" .
