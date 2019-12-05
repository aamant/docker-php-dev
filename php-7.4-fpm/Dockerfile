FROM php:7.4-fpm-buster

ENV PHP_ENABLE_XDEBUG false

RUN pecl install xdebug-2.8.1

RUN apt-get update && apt-get install -y \
		git zip unzip libcurl4-gnutls-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libxslt-dev \
        libzip-dev \
    && docker-php-ext-install -j$(nproc) iconv pdo pdo_mysql zip xsl curl opcache \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

# PHP 
# intl 
RUN apt-get update \
    && apt-get install -y libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

# others
RUN docker-php-ext-install soap bcmath

RUN curl -sL https://deb.nodesource.com/setup_12.x | bash - \
    && apt-get install -y nodejs \
    && npm -g install grunt-cli less sass uglify-js uglifycss

# smtp
RUN apt-get update && apt-get install -y msmtp mailutils
COPY etc/msmtp /etc/msmtprc
RUN chmod 600 /etc/msmtprc

# Clean RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/cache/*

# Composer
RUN curl -sS https://getcomposer.org/installer | \
  php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /sources

ADD docker-entrypoint.sh /docker-entrypoint.sh
RUN ["chmod", "+x", "/docker-entrypoint.sh"]
ENTRYPOINT ["/docker-entrypoint.sh"]

ADD php/custom.ini /usr/local/etc/php/conf.d/xxx-custom.ini
ADD php/php-fpm.conf /usr/local/etc/

CMD ["php-fpm", "-F"]