#!/bin/bash

XDEBUG_CONFIGFILE=/etc/php.d/99-xdebug.ini
if [ "$XDEBUG_ENABLED" = "1" ]; then
  # xdebugを有効にする
  echo "enable xdebug"
  sed -i -e "s/^\;*zend_extension/zend_extension/" $XDEBUG_CONFIGFILE
else
  # xdebugを無効にする
  echo "disable xdebug"
  sed -i -e "s/^zend_extension/\;zend_extension/" $XDEBUG_CONFIGFILE
fi

exec "$@"
