### Zurmo application container
This container is a data only container and offers the zurmo application files (PHP).

#### Volumes
This is the volume which contains all the php files for zurmo:

```
/zurmo
```

#### Add volumes of this container in your container
Basic usage:

```
docker create --name zurmo_application icclabcna/zurmo_application
docker run --volumes-from zurmo_application your_container
```

Typical usage:  
The typical usage is using the zurmo_apache and the zurmo_config container together with this zurmo_application container

```
docker create --name zurmo_application icclabcna/zurmo_application
docker run --name zurmo_apache -p 80:80 --volumes-from zurmo_application --volumes-from zurmo_config icclabcna/zurmo_apache /apache-run.sh
```