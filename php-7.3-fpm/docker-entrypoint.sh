#!/bin/bash

[ "$PHP_ENABLE_XDEBUG" = "true" ] && \
    docker-php-ext-enable xdebug && \
    echo "Xdebug is enabled"

[ "$PHP_ENABLE_BLACKFIRE" = "true" ] && \
    printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > $PHP_INI_DIR/conf.d/blackfire.ini && \
    echo "Xdebug is enabled"

exec "$@"