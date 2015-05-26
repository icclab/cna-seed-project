# Logstash 
This image contains logstash and is used to filter, aggregate and forward events.

## Inputs
Logstash can receive logs from Log-Courier (internal TCP-Port 5000).

It is expected that the logs come in a specific format. These are described in the next sections by type.
### Load Balancer
```
Type=loadbalancer
```
The rest of the fields should be in the format used by HAProxy (v1.5).

### Webserver
```
Type=webserver
```
The webserver annotated events are parsed correctly if:
- they are in the standard apache format 'combined'
- they are in a special custom format:
```
CUSTOMAPACHE %{SYSLOG5424SD:request_received_date} %{NOTSPACE:request_id} %{QS:request_text} %{INT:http_status_code:int} %{SYSLOG5424SD} %{INT:time_serve_request_in_s:int} %{INT:time_serve_request_in_us:int} %{INT:response_size_in_bytes:int} %{IP:remote_host} %{IP:local_ip} %{IP:remote_ip} %{URIPATHPARAM}
```
- they contain application specific logs from zurmo:
```
DURATION \d+(\.\d+)?
ZURMO %{NOTSPACE:request_id} %{DURATION:time_elapsed:float}
```

### Memcached
```
Type=cache
```

Events from memcached are parsed correctly if they appear in the following format:
```
COMMUNICATIONDIR [<>]{1}
MESSAGE .*
MEMCACHED %{COMMUNICATIONDIR:communictaion_direction}?%{INT:connection_socket_descriptor}? %{MESSAGE:message}
```

### System Metrics
```
category=system_metrics
```

System Metrics are also parsed. It is intended that you send values from collectd written out to CSV files.
The following metrics are supported:
#### CPU
metrics:
- cpu_idle
- cpu_softirq
- cpu_user
- cpu_system
- cpu_interrupt
- cpu_wait
- cpu_steal
- cpu_nice

Format
```
{UNIX-Timestamp},{Value}
```

- cpu_load

#### Memory
- memory_buffered
- memory_free
- memory_used
- memory_cached

Format
```
{UNIX-Timestamp},{Value}
```

#### Disk Usage
- free_space
- reserved_space
- used_space

Format
```
{UNIX-Timestamp},{Value}
```


#### Disk I/O
- disk_time
- disk_merged
- disk_octets
- disk_operations

Format
```
{UNIX-Timestamp},{Read-Value},{Write-Value}
```

#### Uptime
- Uptime

Format
```
{UNIX-Timestamp},{Value}
```

## Outputs
All events are forwarded to elasticsearch. The short-term aggregated metrics are additionally written to etcd.


## Aggregation
This logstash instance does aggregate the response times from the load balancers and calculate some metrics of the data.  
Field for metrics: 'time_duration'  
This metric is calculated twice over a different time period:

### Long-term metrics
Flushed: every 5s
Cleared: every 5s

Field set:
```
metric-period=short_term
```

### Short-term metrics
Flushed: every 10s
Cleared: every 10s

Field set:
```
metric-period=long_term
```
