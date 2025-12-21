FROM ghcr.io/roadrunner-server/roadrunner:latest AS roadrunner
FROM php:8.x-cli

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

# Install and configure your application
# ...

CMD rr serve -c .rr.yaml