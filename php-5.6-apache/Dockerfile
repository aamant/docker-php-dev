FROM php:5.6-apache-jessie

RUN pecl install xdebug-2.5.5 \
    && docker-php-ext-enable xdebug

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
    && docker-php-ext-install -j$(nproc) iconv mcrypt pdo pdo_mysql \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && rm -rf /var/lib/apt/lists/*

COPY host.conf /etc/apache2/sites-available/host.conf
COPY custom.ini /usr/local/etc/php/conf.d/custom.ini

RUN a2dissite 000-default
RUN a2ensite host

VOLUME /sources

WORKDIR /sources

CMD /usr/sbin/apache2ctl -D FOREGROUND