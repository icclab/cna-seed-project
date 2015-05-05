#!/bin/bash

elasticdump --input=http://${ELASTICSEARCH}/.kibana --output=$ --type=data --searchBody='{"filter": { "or": [ {"type": {"value": "dashboard"}}, {"type" : {"value":"visualization"}}] }}'
