FROM cravler/dnode-php
MAINTAINER Sergei Vizel <http://github.com/cravler>

# All our dependencies, in alphabetical order (to ease maintenance)
RUN apt-get update && apt-get install -y --no-install-recommends \
        python \
        python-software-properties \
        software-properties-common && \

# Add 3rd Party Repository
    add-apt-repository ppa:chris-lea/node.js && \

# Install node.js
    apt-get update && apt-get install -y --no-install-recommends \
        nodejs && \

# Remove cache
    apt-get clean && rm -rf /var/lib/apt/lists/*

ADD remote-config.sh /.cravler/remote-config.sh
ADD remote-app.sh /.cravler/remote-app.sh
ADD docker-entrypoint.sh /.cravler/entrypoint.sh

EXPOSE 8080 8081 8082
CMD /.cravler/remote-app.sh