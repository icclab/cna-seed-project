#!/bin/bash
# Requirements: installed git and phpize (package php5-dev)

git clone git://github.com/xdebug/xdebug.git
cd xdebug
phpize
./configure --enable-xdebug
make
make install
