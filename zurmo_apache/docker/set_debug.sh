#/bin/bash
# Enables or disables the debugging

PHP_INI_FOLDER=/etc/php5/apache2

if [ $# -lt 1 ]; then
  echo 'Usage: ' $0 '[enable|disable]';
fi

if [ $1 = 'disable' ]; then
  
  mv $PHP_INI_FOLDER/php.ini $PHP_INI_FOLDER/debug_php.ini
  mv $PHP_INI_FOLDER/nodebug_php.ini $PHP_INI_FOLDER/php.ini

elif [ $1 = 'enable' ];then

  mv $PHP_INI_FOLDER/php.ini $PHP_INI_FOLDER/nodebug_php.ini
  mv $PHP_INI_FOLDER/debug_php.ini $PHP_INI_FOLDER/php.ini

else
  
  echo 'do you want to enable or disable!'
  
fi