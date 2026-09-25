FROM php:8.3-apache-bookworm

ENV PHP_ENABLE_XDEBUG=false
ENV PHP_ENABLE_BLACKFIRE=false
ENV MAGENTO_DIR=true
ENV DOCUMENT_ROOT=/var/www/html/pub
ENV COMPOSER_VERSION="latest"
ENV NODE_MAJOR=24
ENV TZ=Europe/Paris

RUN pecl install xdebug

RUN apt-get update && apt-get install -y \
		git zip unzip libcurl4-gnutls-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libxslt-dev \
        libzip-dev \
        moreutils \
#        libmpdec-dev \
    && docker-php-ext-install iconv pdo pdo_mysql zip xsl curl opcache pcntl posix \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd \
    && apt-get install -y libmagickwand-dev \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    #&& docker-php-ext-install decimal \
    && docker-php-ext-install calendar \
    # intl
    && apt-get install -y libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-install ftp exif \
    # others
    && docker-php-ext-install soap bcmath sockets \
    && docker-php-ext-install mysqli \
    # telemetry
    && pecl install opentelemetry protobuf redis \
    && docker-php-ext-enable opentelemetry protobuf redis \
    # cron & network
    && apt install -y cron tzdata texinfo net-tools

# grunt less
RUN apt install -y ca-certificates curl gnupg \
    && mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list \
    && apt update && apt install -y nodejs \
    && npm -g install grunt-cli less sass uglify-js uglifycss

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
# https://devdocs.magento.com/guides/v2.4/performance-best-practices/software.html
# https://devdocs.magento.com/guides/v2.4/install-gde/prereq/php-settings.html
RUN set -eux; \
  { \
  echo '<IfModule mod_expires.c>'; \
  echo '  ExpiresActive On'; \
  echo '  ExpiresByType image/jpg "access plus 1 year"'; \
  echo '  ExpiresByType image/jpeg "access plus 1 year"'; \
  echo '  ExpiresByType image/avif "access plus 1 year"'; \
  echo '  ExpiresByType image/webp "access plus 1 year"'; \
  echo '  ExpiresByType image/png "access plus 1 year"'; \
  echo '  ExpiresByType text/css "access plus 1 month"'; \
  echo '  ExpiresByType text/x-javascript "access plus 1 month"'; \
  echo '  ExpiresByType image/x-icon "access plus 1 year"'; \
  echo '</IfModule>'; \
  } > /etc/apache2/conf-available/cache.conf; \
  a2enconf cache; \
  { \
  echo 'MaxKeepAliveRequests 1000'; \
  echo 'KeepAliveTimeout 2'; \
  } > /etc/apache2/conf-available/keepalive.conf; \
  a2enconf keepalive; \
  docker-php-ext-enable opcache;
# We don't want this kind of cache for local dev
#  { \
#    echo "opcache.huge_code_pages=1"; \
#    echo 'opcache.memory_consumption=512'; \
#    echo 'opcache.interned_strings_buffer=50'; \
#    echo 'opcache.max_accelerated_files=60000'; \
#    echo 'opcache.consistency_checks=0'; \
#    echo 'opcache.validate_timestamps=0'; \
#    echo 'opcache.revalidate_freq=10'; \
#    echo 'opcache.fast_shutdown=1'; \
#    echo 'opcache.jit=1235'; \
#    echo 'opcache.jit_buffer_size=256M'; \
#  } > /usr/local/etc/php/conf.d/opcache-recommended.ini

# https://wordpress.org/support/article/editing-wp-config-php/#configure-error-logging
RUN { \
  # https://www.php.net/manual/en/errorfunc.constants.php
  # https://github.com/docker-library/wordpress/issues/420#issuecomment-517839670
  echo 'error_reporting = -1'; \
  echo 'display_errors = On'; \
  echo 'display_startup_errors = On'; \
  echo 'log_errors = On'; \
  echo 'error_log = /var/log/php.log'; \
  echo 'log_errors_max_len = 1024'; \
  echo 'ignore_repeated_errors = Off'; \
  echo 'ignore_repeated_source = Off'; \
  echo 'html_errors = Off'; \
  } > /usr/local/etc/php/conf.d/error-logging.ini ; \
  { \
  echo 'variables_order = "EGPCS"'; \
  } > /usr/local/etc/php/conf.d/variables-order.ini ; \
  { \
  echo '[apcu]'; \
  echo 'apc.enabled = 1'; \
  echo 'apc.enable_cli = 1'; \
  } > /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini ; \
  docker-php-ext-enable apcu; \
  { \
  echo 'memory_limit = 1G'; \
  echo 'max_input_vars = 100000'; \
  echo 'post_max_size = 50M'; \
  echo 'realpath_cache_size = 10M'; \
  echo 'realpath_cache_ttl = 7200'; \
  echo 'upload_max_filesize = 50M'; \
  echo 'default_socket_timeout = 1200'; \
  } > /usr/local/etc/php/conf.d/limits.ini ; \
  { \
    echo 'sendmail_path = "/usr/bin/msmtp -t"'; \
  } > /usr/local/etc/php/conf.d/smtp.ini

# smtp
#ADD etc/msmtprc /etc/msmtprc
RUN { \
  echo 'defaults'; \
  echo ''; \
  echo 'account maildev'; \
  echo 'host mail'; \
  echo 'port 25'; \
  echo 'from "user@mail.test"'; \
  echo ''; \
  echo 'account default : maildev'; \
  } > /etc/msmtprc

RUN apt-get install -y msmtp mailutils \
  && apt purge -y exim4-base exim4-config \
  && chmod 644 /etc/msmtprc \
  && rm -rf /usr/sbin/sendmail && ln -s /usr/bin/msmtp /usr/sbin/sendmail

# Clean RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/cache/*

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer.phar \
  && \
    { \
      echo '#!/bin/bash'; \
      echo ''; \
      echo '/usr/local/bin/php -d memory_limit=-1 /usr/local/bin/composer.phar "$@"'; \
    } > /usr/local/bin/composer \
  && chmod +x /usr/local/bin/composer

# blackfire
# add env var to docker-compose BLACKFIRE_CLIENT_ID BLACKFIRE_CLIENT_TOKEN
#RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
#    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/amd64/$version \
#    && mkdir -p /tmp/blackfire \
#    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire \
#    && mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get ('extension_dir');")/blackfire.so \
#    && rm -rf /tmp/blackfire /tmp/blackfire-probe.tar.gz
#
#RUN mkdir -p /tmp/blackfire \
#    && curl -A "Docker" -L https://blackfire.io/api/v1/releases/client/linux_static/amd64 | tar zxp -C /tmp/blackfire \
#    && mv /tmp/blackfire/blackfire /usr/bin/blackfire \
#    && rm -Rf /tmp/blackfire

RUN set -eux \
  && a2enmod rewrite expires headers \
  && a2ensite 000-default

RUN \
  { \
    echo 'SetEnv MAGE_IS_DEVELOPER_MODE 1'; \
    echo 'SetEnvIf X-Forwarded-Proto https HTTPS=on'; \
  } > /etc/apache2/conf-enabled/magento.conf

WORKDIR /var/www/html/

ADD docker-entrypoint.sh /docker-entrypoint.sh
RUN ["chmod", "+x", "/docker-entrypoint.sh"]
ENTRYPOINT ["/docker-entrypoint.sh"]

#ADD php/custom.ini /usr/local/etc/php/conf.d/xxx-magento.ini

CMD ["apache2-foreground"]
