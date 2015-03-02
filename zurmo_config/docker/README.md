### Zurmo application configuration container
This container is a data only container and offers volumes for the configuration files of zurmo.

#### Volumes
This is the basic volume which contains all the php configuration files for zurmo:

```
/zurmo/zurmo/app/protected/config
```

The following volumes can be used with conf.d to automatically adapt the configuration when the location/port of the database
changes or memcache servers are added/removed:

```
/etc/confd/templates
/etc/confd/conf.d
```

#### Confd configuration
The configuration files of confd listens for the etcd keys:

```
/services/database
/services/cache
```

Keys used in the configuration:

```
/services/database/host
/services/database/port
/services/cache/any-identifier/host
/services/cache/any-identifier/port
```

#### Add volumes of this container in your container
Basic usage:

```
docker create --name zurmo_config icclabcna/zurmo_config
docker run --volumes-from zurmo_config your_container
```

Typical usage:  
The typical usage is using the zurmo_apache and the zurmo_application container together with this zurmo_config container

```
docker create --name zurmo_config icclabcna/zurmo_config
docker run --name zurmo_apache -p 80:80 --volumes-from zurmo_application --volumes-from zurmo_config icclabcna/zurmo_apache /apache-run.sh
```