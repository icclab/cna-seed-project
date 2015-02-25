### Elasticsearch image
This image runs elasticsearch and allows connections from any host.

#### Ports
- 9200: HTTP
- 9300: transport

#### Example usage
```
docker run --name zurmo_elasticsearch -p 9200:9200 icclabcna/zurmo_elasticsearch
```