#!/bin/bash

wget http://tsung.erlang-projects.org/dist/tsung-1.5.1.tar.gz
tar -zxvf tsung-1.5.1.tar.gz
cd tsung-1.5.1
./configure
make
make install
