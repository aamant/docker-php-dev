FROM debian:buster-slim
ENV DEBIAN_FRONTEND noninteractive


#RUN echo "deb http://archive.debian.org/debian/ stretch main" > /etc/apt/sources.list
#RUN echo "deb-src http://archive.debian.org/debian/ stretch main" >> /etc/apt/sources.list

#
# install varnish build deps
#
RUN apt-get update
#RUN  apt-get install -y perl libdpkg-perl libbz2-1.0 bzip2 dpkg-dev sgml-base xml-core
#RUN  apt-get install -y libldap-2.4-2 libhogweed4 librtmp1 libcurl3 libgnutls30 libcurl3-gnutls
RUN  apt-get install -y \
#    autoconf \
#    gcc g++ \
#    cpp \
    docutils-common \
    automake \
    autotools-dev \
    build-essential \
    ca-certificates \
    curl \
    git \
    libedit-dev \
    libgeoip-dev \
    libjemalloc-dev \
    libmhash-dev \
    libncurses-dev \
    libpcre3-dev \
    libtool \
    pkg-config \
    python-docutils \
    python-sphinx \
    && apt-get clean \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/*

#
# install varnish
#
ENV VARNISH_VERSION=4.1.11
ENV VARNISH_SHA256SUM=f937a45116f3a7fbb38b2b5d7137658a4846409630bb9eccdbbb240e1a1379bc

RUN mkdir -p /usr/local/src && \
    cd /usr/local/src && \
    curl -sfLO http://varnish-cache.org/_downloads/varnish-${VARNISH_VERSION}.tgz && \
    echo "${VARNISH_SHA256SUM} varnish-${VARNISH_VERSION}.tgz" | sha256sum -c - && \
    tar -xzf varnish-${VARNISH_VERSION}.tgz && \
    cd varnish-${VARNISH_VERSION} && \
    ./autogen.sh && \
    ./configure && \
    make install && \
    cd /usr/local/src && \
    rm -rf varnish-*

#
# install stock varnish module library
#
ENV VARNISHMODULES_VERSION=0.15.0
ENV VARNISHMODULES_SHA256SUM=8c03a13c348127e11b317006b9206d0b94c79d3f775f337b8fa1da818cfd2482

RUN cd /usr/local/src/ && \
    curl -sfL https://github.com/varnish/varnish-modules/archive/${VARNISHMODULES_VERSION}.tar.gz \
        -o varnish-modules-${VARNISHMODULES_VERSION}.tar.gz && \
    echo "${VARNISHMODULES_SHA256SUM} varnish-modules-${VARNISHMODULES_VERSION}.tar.gz" | sha256sum -c - && \
    tar -xzf varnish-modules-${VARNISHMODULES_VERSION}.tar.gz && \
    cd varnish-modules-${VARNISHMODULES_VERSION} && \
    ./bootstrap && \
    ./configure && \
    make install && \
    cd /usr/local/src && \
    rm -rf varnish-modules-${VARNISHMODULES_VERSION}* && \
    ldconfig

#
# install libvmod-dynamic
#
ENV LIBVMOD_DYNAMIC_BRANCH=4.1
ENV LIBVMOD_DYNAMIC_COMMIT=ff723e3d97bfaf2a6309e0ba032a903de700b5a2

RUN cd /usr/local/src/ && \
    git clone -b ${LIBVMOD_DYNAMIC_BRANCH} https://github.com/nigoroll/libvmod-dynamic.git && \
    cd libvmod-dynamic && \
    git reset --hard ${LIBVMOD_DYNAMIC_COMMIT} && \
    ./autogen.sh && \
    ./configure && \
    make install && \
    cd /usr/local/src && \
    rm -rf libvmod-dynamic && \
    ldconfig

#
# install libvmod-digest
#
ENV LIBVMOD_DIGEST_VERSION=1.0.2
ENV LIBVMOD_DIGEST_SHA256SUM=9cbe87f1990282ee25e06af5a19f2217638ce96cf3f155c0f5e7c79bad6afdea

RUN cd /usr/local/src/ && \
    curl -sfLO https://github.com/varnish/libvmod-digest/archive/libvmod-digest-${LIBVMOD_DIGEST_VERSION}.tar.gz && \
    echo "${LIBVMOD_DIGEST_SHA256SUM} libvmod-digest-${LIBVMOD_DIGEST_VERSION}.tar.gz" | sha256sum -c - && \
    tar -xzf libvmod-digest-${LIBVMOD_DIGEST_VERSION}.tar.gz && \
    cd libvmod-digest-libvmod-digest-${LIBVMOD_DIGEST_VERSION} && \
    ./autogen.sh && \
    ./configure && \
    make install && \
    cd /usr/local/src && \
    rm -rf libvmod-digest* && \
    ldconfig

#
# install libvmod-geoip
#
ENV LIBVMOD_GEOIP_BRANCH=master
ENV LIBVMOD_GEOIP_COMMIT=b4d72ecc23895d4a0e9b28655093861f0c85cb66

RUN cd /usr/local/src/ && \
    git clone -b ${LIBVMOD_GEOIP_BRANCH} https://github.com/varnish/libvmod-geoip.git && \
    cd libvmod-geoip && \
    git reset --hard ${LIBVMOD_GEOIP_COMMIT} && \
    ./autogen.sh && \
    ./configure && \
    make install && \
    cd /usr/local/src && \
    rm -rf libvmod-geoip && \
    ldconfig

# init
COPY init.sh /init.sh

RUN useradd -r -s /bin/false vcache
RUN mkdir /etc/varnish

ENV VARNISH_CONFIG  /etc/varnish/default.vcl
ENV VARNISH_STORAGE malloc,100m
ENV VARNISH_LISTEN  :80
ENV VARNISH_MANAGEMENT_LISTEN 127.0.0.1:6082

EXPOSE 80
EXPOSE 6082

CMD ["/init.sh"]
