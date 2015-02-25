### Kibana image
This image runs nginx to offer kibana.
It uses confd to discover the elasticsearch instance.

#### Confd
When the container starts it waits for a elasticsearch instance to register itself to etcd on:

```
/services/elasticsearch
```

It then uses the information:

```
/services/elasticsearch/host
/services/elasticsearch/port
```

Confd needs etcd to be running on 10.1.42.1:4001 (default settings). If you want to change this, define the environment variables ETCD_PORT and HOST_IP.

#### Example usage
```
docker run --name zurmo_kibana -p 80:80 icclabcna/zurmo_kibana /start.sh
```