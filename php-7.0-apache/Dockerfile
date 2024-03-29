FROM php:7.0-apache-stretch

ENV PHP_ENABLE_XDEBUG false
ENV MAGENTO_DIR true
ENV DOCUMENT_ROOT=/var/www/html/pub
ENV LOGLEVEL="info ssl:warn"

RUN pecl install xdebug-2.7.2

# RUN echo "deb http://deb.debian.org/debian stretch-backports main contrib non-free" > /etc/apt/sources.list
RUN echo "deb http://archive.debian.org/debian/ stretch main" > /etc/apt/sources.list
RUN echo "deb-src http://archive.debian.org/debian/ stretch main" >> /etc/apt/sources.list

RUN apt-get update && apt-get install -y \
		git zip unzip libcurl4-gnutls-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        libxslt-dev \
        exiftool \
    && docker-php-ext-install -j$(nproc) iconv mcrypt pdo pdo_mysql zip xsl curl opcache exif \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable exif

# PHP 
# intl 
RUN apt-get update \
    && apt-get install -y libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

# others
RUN docker-php-ext-install soap bcmath

# grunt less
RUN curl -sL https://deb.nodesource.com/setup_12.x | bash - \
    && apt-get install -y --force-yes nodejs \
    && npm -g install grunt-cli less uglify-js uglifycss

# ssmtp
RUN apt-get update && apt-get install -y ssmtp mailutils
COPY ssmtp/ssmtp.conf /etc/ssmtp/ssmtp.conf
COPY ssmtp/revaliases /etc/ssmtp/revaliases

# Clean RUN apt-get clean
RUN apt-get autoremove -y
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