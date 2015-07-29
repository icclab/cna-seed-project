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

## Starting when initializing a cluster
Usually you want to start the dynamite container just after your cluster is ready. You can do that by declaring a service in the cloud-config. The following service will download the fleet service to start dynamite. You can write and provide your own or use [the template](https://github.com/icclab/cna-seed-project/blob/master/zurmo_dynamite/fleet/zurmo_dynamite.service). This init service waits until etcd and fleet are running and etcd is an a healthy state. It also ensures that the dynamite service is started only on one node. 

```
  - name: app-init.service
    command: start
    content: |
      [Unit]
      Description=Start script that is executed only on one node
      After=fleet.service
      Requires=fleet.service

      [Service]
      RemainAfterExit=true
      Restart=on-failure
      EnvironmentFile=/etc/environment
      ExecStartPre=/bin/bash -c 'wget https://url.to/dynamite.service -O /tmp/dynamite.service'
      ExecStart=/bin/bash -c 'STATUS_CODE=$(curl -L -o /dev/null -w "%{http_code}" http://127.0.0.1:4001/v2/keys/ --silent); \
      RUNNING_NODES=0; \
      while [[ $STATUS_CODE > 399 ]]; \
      do \
              echo "ETCD not available yet. Waiting..."; \
              STATUS_CODE=$(curl -L -o /dev/null -w "%{http_code}" http://127.0.0.1:4001/v2/keys/ --silent); \
              sleep 1; \
      done; \
      while [[ $RUNNING_NODES < 3 ]]; \
      do \
              echo "Wait for at least 3 nodes in etcd cluster become healthy"; \
              RUNNING_NODES=$(etcdctl cluster-health | sed 1d | grep -c " healthy"); \
      done; \
      fleetctl list-units > /dev/null; \
      while [[ $? != 0 ]]; \
      do \
        echo "Fleet not available yet. Waiting..."; \
        fleetctl list-units > /dev/null; \
      done; \
      echo "Fleet is available."; \
      STATUS_CODE=$(curl -L http://127.0.0.1:4001/v2/keys/init?prevExist=false -XPUT -d "%H - %m - %b" -o /dev/null -w "%{http_code}" --silent); \
      if [[ $STATUS_CODE < 400 ]]; then \
              echo "Initializing Cluster"; \
              fleetctl load /tmp/zurmo_dynamite.service; \
              fleetctl start zurmo_dynamite.service; \
      else \
              echo "Cluster initialized already. Do nothing"; \
      fi \
      '
      [Install]
      WantedBy=multi-user.target
      ```
