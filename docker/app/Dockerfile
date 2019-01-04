# Pull base image
FROM byteworks/phalcon-php72-nginx:ubuntu-18.04

ENV PROVISION_CONTEXT "development"

# Deploy scripts/configurations
COPY bin/*.sh /opt/docker/provision/entrypoint.d/

RUN mkdir -p /vendor \
    && composer --working-dir=/vendor require phalcon/devtools \
    && ln -s /vendor/vendor/phalcon/devtools/phalcon.php /usr/local/bin/phalcon \
    && chmod ugo+x /usr/local/bin/phalcon \
    # Custom provisions
    && chmod +x /opt/docker/provision/entrypoint.d/*.sh \
