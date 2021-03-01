FROM php:5.6-fpm-jessie

ENV PHP_ENABLE_XDEBUG false

COPY sources.list /etc/apt/sources.list

RUN pecl install xdebug-2.5.5 \
    && docker-php-ext-enable xdebug

RUN apt-get update
RUN apt-get install -y --force-yes git zip unzip libcurl4-gnutls-dev \
            libfreetype6-dev \
            libjpeg62-turbo-dev \
            libmcrypt-dev \
            libpng-dev \
            libxslt-dev

RUN docker-php-ext-install -j$(nproc) iconv mcrypt pdo pdo_mysql zip xsl curl opcache
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/
RUN docker-php-ext-install -j$(nproc) gd

# PHP
# intl
RUN apt-get update \
    && apt-get install -y libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

# others
RUN docker-php-ext-install \
    soap

# Clean RUN apt-get clean
RUN apt-get autoremove -y
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/cache/*

WORKDIR /sources

ADD docker-entrypoint.sh /docker-entrypoint.sh
RUN ["chmod", "+x", "/docker-entrypoint.sh"]
ENTRYPOINT ["/docker-entrypoint.sh"]

CMD ["php-fpm", "-F"]