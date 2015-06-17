# Logstash 
This image contains logstash and is used to filter, aggregate and forward events.

## Ports
- Listening on TCP-Port 5000 for log-courier input events
- Listening on TCP-Port 9300 for bi-directional elasticsearch communication

## Inputs
Logstash can only receive logs from Log-Courier.

It is expected that the logs come in a specific format. These are described in the next sections by type.
### Load Balancer
Required field:
```
Type=loadbalancer
```
The rest of the fields should be in the format used by HAProxy (v1.5).

### Webserver
```
Type=webserver
```
The webserver annotated events are parsed correctly if:
#### they are in the standard apache format 'combined'
Example:
```
172.17.8.101 - - [27/May/2015:09:48:56 +0000] "GET /zurmo/app/index.php/min/serve/g/js/lm/1432627852 HTTP/1.1" 304 238 "http://core-01/zurmo/app/index.php/zurmo/default/login" "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36"
```

Added Fields:
```
- agent                # User-Agent
- bytes
- clientip
- host                 # Host name
- httpversion
- ident
- referrer
- request
- response
- timestamp
- type
- verb
```

#### they are in this custom apache format:
Apache Log Format:
```
LogFormat "%t %{UNIQUE_ID}e \"%r\" %>s %t %T %D %B %h %A %a %U %q" custom_format
```
Example:
```
[27/May/2015:09:48:56 +0000] VWWTCAoBAAYAAAAwfwIAAAAD "GET /zurmo/app/index.php/min/serve/g/js/lm/1432627852 HTTP/1.1" 304 [27/May/2015:09:48:56 +0000] 0 145076 0 172.17.8.101 10.1.0.6 172.17.8.101 /zurmo/app/index.php/min/serve/g/js/lm/1432627852 
```

Added fields:
```
- time_serve_request_in_s
- time_serve_request_in_us
- response_size_in_bytes
- request_text
- request_received_date
- request_id
- remote_ip
- remote_host
- local_ip
- http_status_code
- host
```
#### they contain application specific logs from zurmo:
Format:
```
{Request_Id} {Time_elapsed}
```
Example:
```
VWWTBwoBAAYAAAA0CGMAAAAG 0.042580842971802
```
Added Fields:
```
- Request_Id
```

### Memcached
Required Field:
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
Required Field:
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
Added Fields
```
- epoch
- value
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
Added Fields
```
- epoch
- value
```

#### Disk Usage
- free_space
- reserved_space
- used_space

Format
```
{UNIX-Timestamp},{Value}
```
Added Fields
```
- epoch
- value
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
Added Fields
```
- epoch
- read
- write
```

#### Uptime
- Uptime

Format
```
{UNIX-Timestamp},{Value}
```
Added Fields
```
- epoch
- value
```
## Outputs
All events are forwarded to elasticsearch. The short-term aggregated metrics are additionally written to etcd.

### Elasticsearch
Elasticsearch is found by using service discovery with etcd. The following paths are accessed:
```
/services/logstorage/%UUID/ip
/services/logstorage/%UUID/transport-port
```
The node name which is used in the elasticsearch cluster is dependend of the ip:
```
logstash_{$PRIVATE_IP}
```

### Etcd
#### System metrics
CPU and memory metrics are written to etcd. These events are found by resource field:
```
resource=cpu
```
```
resource=memory
```
The values of the metrics are written into the following path:
```
/services/[type]/[service-id]/metrics/[resource]/[metric]/[time_collected]
```
The square bracket fields are replaced dynamically with the value of the field in the received event.
- type: service type such as webserver, loadbalancer etc.
- service-id: the uuid of the service
- resource: the component where the events are collected from such as cpu, memory etc.
- metric: the name of the metric such as cpu_load, cpu_user, memory_free etc.
- time_collected: the timestamp when this event was collected

Example:
```
/services/webserver/ac396664-4503-4d5a-983b-6dc50c9ff6ae/metrics/cpu/cpu_user/2015-05-27T11:53:15.685Z
```

#### Response times
The response times of haproxy are aggregated and written to:
```
/services/loadbalancer/metrics/response_time/[@timestamp]
```
The keys are the timestamp of the event. The content consists of the whole metrics event which contains average, min, max, standard deviation and several percentile values.

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
## Starting a Container
The following command starts the logstash container whose Ports are mapped 1:1 to the host.  
You should also set the Environment Variables for the etcd port and ip as well as the ip of the host.
```
docker run --name zurmo_logstash \
  -p 5000:5000 \
  -p 9300:9300 \ 
  -e "ETCD_IP=10.1.42.1" \
  -e "ETCD_PORT=4001" \
  -e "HOST_PRIVATE_IPV4=10.1.42.1" \
  icclabcna/zurmo_logstash
```
