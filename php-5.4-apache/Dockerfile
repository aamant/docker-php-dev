FROM php:5.4-apache

COPY sources.list /etc/apt/sources.list

RUN pecl install xdebug-2.4.1 \
    && docker-php-ext-enable xdebug

RUN apt-get update && apt-get install -y \
		git zip unzip libcurl4-gnutls-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        libxslt-dev

RUN docker-php-ext-install iconv mcrypt pdo pdo_mysql zip xsl curl

RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd

# PHP
# intl
RUN apt-get update \
    && apt-get install -y libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

# others
RUN docker-php-ext-install \
    soap

# grunt less
RUN curl -sL https://deb.nodesource.com/setup_9.x | bash - \
    && apt install -y nodejs \
    && npm -g install grunt-cli less uglify-js uglifycss


COPY host.conf /etc/apache2/sites-available/host.conf
COPY custom.ini /usr/local/etc/php/conf.d/custom.ini

RUN a2enmod rewrite
RUN a2dissite 000-default
RUN a2ensite host

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php  --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Clean RUN apt-get clean
RUN apt-get autoremove -y
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/cache/*

WORKDIR /sources

CMD rm -f /var/run/apache2/apache2.pid \
    && /usr/sbin/apache2ctl -D FOREGROUND