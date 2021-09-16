#!/bin/bash

/usr/local/bin/php -d memory_limit=-1 /usr/local/bin/composer.phar "$@"