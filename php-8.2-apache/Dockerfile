FROM php:8.2-apache-bullseye

ENV PHP_ENABLE_XDEBUG false
ENV PHP_ENABLE_BLACKFIRE false
ENV MAGENTO_DIR true
ENV DOCUMENT_ROOT=/var/www/html/pub
ENV COMPOSER_VERSION = "latest"
ENV NODE_MAJOR=20

RUN pecl install xdebug-3.2.1

RUN apt-get update && apt-get install -y \
		git zip unzip libcurl4-gnutls-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libxslt-dev \
        libzip-dev \
        libmpdec-dev \
    && docker-php-ext-install iconv pdo pdo_mysql zip xsl curl opcache \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

#RUN docker-php-ext-install decimal

# PHP 
# intl 
RUN apt-get update \
    && apt-get install -y libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

# others
RUN docker-php-ext-install soap bcmath sockets

# grunt less
RUN apt update && apt install -y ca-certificates curl gnupg
RUN mkdir -p /etc/apt/keyrings
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg

RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list
RUN apt update && apt install -y nodejs \
    && npm -g install grunt-cli less sass uglify-js uglifycss

# smtp
RUN apt-get update && apt-get install -y msmtp mailutils
RUN apt purge -y exim4-base exim4-config
ADD etc/msmtprc /etc/msmtprc
RUN chmod 644 /etc/msmtprc
RUN rm -rf /usr/sbin/sendmail && ln -s /usr/bin/msmtp /usr/sbin/sendmail

# Clean RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/cache/*

# Composer
RUN curl -sS https://getcomposer.org/installer | \
  php -- --install-dir=/usr/local/bin --filename=composer.phar
RUN \
  { \
    echo '#!/bin/bash'; \
    echo ''; \
    echo '/usr/local/bin/php -d memory_limit=-1 /usr/local/bin/composer.phar "$@"'; \
  } > /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

# blackfire
# add env var to docker-compose BLACKFIRE_CLIENT_ID BLACKFIRE_CLIENT_TOKEN
RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/amd64/$version \
    && mkdir -p /tmp/blackfire \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get ('extension_dir');")/blackfire.so \
    && rm -rf /tmp/blackfire /tmp/blackfire-probe.tar.gz

RUN mkdir -p /tmp/blackfire \
    && curl -A "Docker" -L https://blackfire.io/api/v1/releases/client/linux_static/amd64 | tar zxp -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire /usr/bin/blackfire \
    && rm -Rf /tmp/blackfire

RUN set -eux \
  && a2enmod rewrite expires \
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

ADD php/custom.ini /usr/local/etc/php/conf.d/xxx-magento.ini

CMD ["apache2-foreground"]