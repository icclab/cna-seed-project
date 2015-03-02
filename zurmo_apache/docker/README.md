### Apache container
This container is an apache container to run zurmo with.
It contains PHP with all modules needed by zurmo and the configuration files are
modified to the requirements of zurmo.

#### Zurmo
The zurmo application is not included in the container. The webservers root points to /zurmo.
So if you want to use this container, either map the volume to that path or use the volumes of the zurmo_application container for this.

#### Confd
The start script will run confd to modify the zurmo configuration dynamically. The best way to use this image is to mount the volumes from the image zurmo_config.

Confd needs etcd to be running on 10.1.42.1:4001 (default settings). If you want to change this, define the environment variables ETCD_PORT and HOST_IP.

#### Example usage
Usually this container is used with the data only containers zurmo_config and zurmo_application. For the configuration of the zurmo application please read the readme of the 
zurmo_config image.

```
docker create --name zurmo_config icclabcna/zurmo_config
docker create --name zurmo_application icclabcna/zurmo_application
docker run --name zurmo_apache -p 80:80 --volumes-from zurmo_application --volumes-from zurmo_config icclabcna/zurmo_apache /apache-run.sh
```