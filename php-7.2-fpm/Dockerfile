FROM php:7.2-fpm-stretch

ENV PHP_ENABLE_XDEBUG false
ENV MAGENTO_DIR true

RUN pecl install xdebug-3.0.4

RUN apt-get update && apt-get install -y \
		git zip unzip libcurl4-gnutls-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libxslt-dev \
    && docker-php-ext-install -j$(nproc) iconv pdo pdo_mysql zip xsl curl opcache \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

# PHP 
# intl 
RUN apt-get update \
    && apt-get install -y libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

RUN echo "deb http://deb.debian.org/debian stretch-backports main" >> /etc/apt/sources.list
RUN apt update && apt-get -t stretch-backports install -y libsodium-dev
RUN docker-php-ext-configure sodium \
    && docker-php-ext-install sodium

# others
RUN docker-php-ext-install soap bcmath

# grunt less
RUN curl -sL https://deb.nodesource.com/setup_10.x | bash - \
    && apt install -y nodejs \
    && npm -g install grunt-cli less sass uglify-js uglifycss

# ssmtp
RUN apt-get update && apt-get install -y ssmtp mailutils
COPY ssmtp/ssmtp.conf /etc/ssmtp/ssmtp.conf
COPY ssmtp/revaliases /etc/ssmtp/revaliases

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

WORKDIR /sources

ADD docker-entrypoint.sh /docker-entrypoint.sh
RUN ["chmod", "+x", "/docker-entrypoint.sh"]
ENTRYPOINT ["/docker-entrypoint.sh"]

ADD php/custom.ini /usr/local/etc/php/conf.d/xxx-custom.ini
ADD php/php-fpm.conf /usr/local/etc/

CMD ["php-fpm", "-F"]