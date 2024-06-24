FROM jrei/systemd-ubuntu:20.04

RUN mkdir -p /opt/veriscope

WORKDIR /opt/veriscope

ENV DEBIAN_FRONTEND noninteractive

ENV TZ=America/New_York

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

ENV WWW_GROUP=www-data
ENV SERVICE_USER=serviceuser
ENV UID 1000
ENV GID 1000

RUN adduser \
    --disabled-password \
    --gecos "" \
    --home "$(pwd)" \
    --ingroup "$WWW_GROUP" \
    --no-create-home \
    --uid "$UID" \
    "$SERVICE_USER"

COPY ./ /opt/veriscope

RUN chmod +x /opt/veriscope/scripts/setup-vasp.sh
EXPOSE 80 443
