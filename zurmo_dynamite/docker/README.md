# Dynamite 
This image contains [dynamite](https://github.com/icclab/dynamite) - a scaling engine for CoreOS

## Starting a Container
The following command starts the dynamite container. There are a lot of environment variables to configure dynamite:
  * __ETCD_ENDPOINT__: The etcd HTTP-API endpoint (ip:port)
  * __FLEET_IP__: The fleet HTTP-API ip
  * __FLEET_PORT__: The fleet HTTP-API port
  * __DYNAMITE_DOWNLOAD_FILES__: Boolean flag if the fleet files and the configuration file should be downloaded into the container
  * __DYNAMITE_FLEET_FILES_URL__: A plain text file where each line is a URL to a fleet file. These files will be downloaded and stored in the container.
  * __DYNAMITE_CONFIG_URL__: The URL to the dynamite configuration file. It will be downloaded and stored in the container.

The configuration file and the fleet files will be downloaded (if enabled) to /etc/dynamite. If the download option is set to 'False', you shold mount the required files (fleet files and dynamite configuration) to the following paths:
  * __Configuration File__: /etc/dynamite/dynamite.yaml
  * __Fleet Files__: /etc/dynamite/fleet-files

```
docker run --name dynamite_container \
    -e "ETCD_ENDPOINT=127.0.0.1:4001" \
    -e "FLEET_PORT=49153" \
    -e "FLEET_IP=127.0.0.1" \
    -e "DYNAMITE_DOWNLOAD_FILES=True" \
    -e "DYNAMITE_FLEET_FILES_URL=https://raw.githubusercontent.com/icclab/cna-seed-project/master/init/fleet-files"
    -e "DYNAMITE_CONFIG_URL=https://raw.githubusercontent.com/icclab/cna-seed-project/master/init/dynamite.yaml"
    -v /tmp/dynamite:/etc/dynamite \
    icclabcna/zurmo_dynamite'
```
