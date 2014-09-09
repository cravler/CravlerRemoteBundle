FROM cravler/php5-fpm
MAINTAINER Sergei Vizel <http://github.com/cravler>

# Let the conatiner know that there is no tty
ENV DEBIAN_FRONTEND noninteractive

# Common environment variables
ENV CONF_HOST_PHP5_FPM php
ENV CONF_PORT_PHP5_FPM 9000

# All our dependencies, in alphabetical order (to ease maintenance)
RUN apt-get update && apt-get install -y --no-install-recommends \
        g++ \
        php-pear \
        php5-dev \
        pkg-config \
        libcurl4-openssl-dev \
        libevent-dev \
        make \
        python \
        python-software-properties \
        software-properties-common && \

# Install pecl event extension
    pecl install event && \
    sh -c "echo 'extension=event.so' > /etc/php5/cli/conf.d/20-event.ini" && \

# Add 3rd Party Repository
    add-apt-repository ppa:chris-lea/node.js && \

# Install node.js
    apt-get update && apt-get install -y --no-install-recommends \
        nodejs && \

# Remove cache
    apt-get clean && rm -rf /var/lib/apt/lists/*

ADD docker-entrypoint.sh /cravler-remote.sh
ENTRYPOINT ["/cravler-remote.sh"]

EXPOSE 80
CMD cravler_remote_run_app