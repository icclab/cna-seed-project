### Log-courier for apache
This docker image is a log-courier service that ships apache logs to logstash instances.

The log-files for apache (access- and error-log) are expected to be in these paths:
```
/var/log/apache2/access.log
/var/log/apache2/error.log
```

Additionally a custom log-file of apache is expected to be written here:
```
/var/log/apache2/perf.log
```

For the application logs
```
/zurmo/zurmo/app/protected/runtime/application.log
/zurmo/zurmo/app/protected/runtime/sqlProfiling.log
/zurmo/zurmo/app/protected/runtime/memcacheProfiling.log
/zurmo/zurmo/app/protected/runtime/pageProfiling.log
```
