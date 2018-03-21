FROM php:7.0-apache-jessie

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
    soap

# grunt less
RUN curl -sL https://deb.nodesource.com/setup_9.x | bash - \
    && apt install -y nodejs \
    && npm -g install grunt-cli less uglify-js uglifycss

# Clean RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/cache/*

COPY host.conf /etc/apache2/sites-available/host.conf
COPY custom.ini /usr/local/etc/php/conf.d/custom.ini

RUN a2enmod rewrite
RUN a2dissite 000-default
RUN a2ensite host

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php  --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

WORKDIR /sources

CMD /usr/sbin/apache2ctl -D FOREGROUND