#!/bin/bash

set -e

[ "$PHP_ENABLE_XDEBUG" = "true" ] && \
    docker-php-ext-enable xdebug && \
    echo "Xdebug is enabled"

[ "$PHP_ENABLE_BLACKFIRE" = "true" ] && \
    printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > $PHP_INI_DIR/conf.d/blackfire.ini && \
    echo "Blackfire is enabled"

[ "$MAGENTO_DIR" = "true" ] \
    && chown -R www-data var \
    && chown -R www-data generated \
    && chown -R www-data pub/static

echo "Change document root to $DOCUMENT_ROOT"
sed -e "s#DocumentRoot.*#DocumentRoot $DOCUMENT_ROOT#" -i /etc/apache2/sites-available/000-default.conf;

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- apache2-foreground "$@"
fi

exec "$@"