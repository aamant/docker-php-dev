FROM php:7.0-fpm-jessie

ENV PHP_ENABLE_XDEBUG false
ENV MAGENTO_DIR true

RUN pecl install xdebug-2.7.2

RUN apt-get update && apt-get install -y \
		git zip unzip libcurl4-gnutls-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        libxslt-dev \
    && docker-php-ext-install -j$(nproc) iconv mcrypt pdo pdo_mysql zip xsl curl opcache \
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

# grunt less
RUN curl -sL https://deb.nodesource.com/setup_9.x | bash - \
    && apt install -y nodejs \
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
  php -- --install-dir=/usr/local/bin --filename=composer --1

WORKDIR /sources

ADD docker-entrypoint.sh /docker-entrypoint.sh
RUN ["chmod", "+x", "/docker-entrypoint.sh"]
ENTRYPOINT ["/docker-entrypoint.sh"]

ADD php/custom.ini /usr/local/etc/php/conf.d/xxx-custom.ini
ADD php/php-fpm.conf /usr/local/etc/

CMD ["php-fpm", "-F"]