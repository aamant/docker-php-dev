FROM php:7.1-apache-jessie

RUN pecl install xdebug-2.5.5 \
    && docker-php-ext-enable xdebug

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
RUN docker-php-ext-install \
    soap \
    bcmath

# nodejs
RUN curl -sL https://deb.nodesource.com/setup_9.x | bash - \
    && apt-get install -y nodejs \
    && npm -g install grunt-cli less sass uglify-js uglifycss

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - && \
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list && \
    apt-get update && apt-get install -y yarn


COPY host.conf /etc/apache2/sites-available/host.conf
COPY custom.ini /usr/local/etc/php/conf.d/custom.ini

RUN a2enmod rewrite
RUN a2dissite 000-default
RUN a2ensite host

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === '93b54496392c062774670ac18b134c3b3a95e5a5e5c8f1a9f115f203b75bf9a129d5daa8ba6a13e2cc8a1da0806388a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php  --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Clean RUN apt-get clean
RUN apt-get autoremove -y
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/cache/*

WORKDIR /sources

CMD composer self-update \
    && rm -f /var/run/apache2/apache2.pid \
    && /usr/sbin/apache2ctl -D FOREGROUND