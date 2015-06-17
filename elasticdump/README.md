# Elasticdump
In this folder you find some helper scripts to import/export kibana visualizations/dashboards to/from elasticsearch

## Import the default dashboard
You don't even need to clone the repository. Just execute the following command with the IP where your elasticsearch is running:

```
export EL_IP=<ELASTICSEARCH_IP> && curl -O https://raw.githubusercontent.com/icclab/cna-seed-project/master/elasticdump/data/zurmo.dashboard && curl -O https://raw.githubusercontent.com/icclab/cna-seed-project/master/elasticdump/import_kibana_data.sh && chmod +x import_kibana_data.sh && ./import_kibana_data.sh `pwd`/zurmo.dashboard ${EL_IP}:9200
```
