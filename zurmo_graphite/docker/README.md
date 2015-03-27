graphite-docker
=================

A Docker image for graphite.

Run:
```
docker run -p 8080:8080 -p 2030:2030 -p 2040:2040 -P -d --name zurmo_graphite icclabcna/zurmo_graphite
```

Ports:
- 8080: Web-Interface
- 2030: Send metrics plaintext
- 2040: Send metrics via pickle protocol


== TODO
- Service Announcer
