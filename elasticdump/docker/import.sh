#!/bin/bash

elasticdump --input=/dump.json --output=http://${ELASTICSEARCH}/.kibana --type=data
