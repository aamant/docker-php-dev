FROM aamant/php-7.2-apache

# install the xhprof extension to profile requests
RUN curl "http://pecl.php.net/get/xhprof-2.3.9.tgz" -fsL -o ./xhprof-2.3.9.tgz && \
    mkdir /var/xhprof && tar xf ./xhprof-2.3.9.tgz -C /var/xhprof && \
    cd /var/xhprof/xhprof-2.3.9/extension && \
    phpize && \
    ./configure && \
    make && \
    make install

# custom settings for xhprof
COPY ./xhprof.ini /usr/local/etc/php/conf.d/xhprof.ini

RUN docker-php-ext-enable xhprof

#folder for xhprof profiles (same as in file xhprof.ini)
RUN mkdir -m 777 /profiles