FROM ghcr.io/roadrunner-server/roadrunner:latest AS roadrunner
FROM php:8.3-cli

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

WORKDIR /app

CMD rr serve -c .rr.yaml