#!/bin/bash

[ "$PHP_ENABLE_XDEBUG" = "true" ] && \
    docker-php-ext-enable xdebug && \
    echo "Xdebug is enabled"

[ "$MAGENTO_DIR" = "true" ] \
    && chown -R www-data var \
    && chown -R www-data pub/static

exec "$@"