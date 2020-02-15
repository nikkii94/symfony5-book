ARG NGINX_VERSION
ARG FROM="nginx:$NGINX_VERSION"

FROM $FROM

ARG NGINX_HOST
ARG PROJECT_ROOT
ENV NGINX_HOST=$NGINX_HOST
ENV PROJECT_ROOT=$PROJECT_ROOT

RUN apt-get update && apt-get install --no-install-recommends -y \
        openssl \
    && rm -rf /var/lib/apt/lists/*

COPY ./conf/openssl.cnf /etc/ssl/openssl.cnf
RUN sed -i -e "s/NGINX_HOST/$NGINX_HOST/g" /etc/ssl/openssl.cnf

RUN openssl genrsa -des3 -passout pass:NotSecure -out /etc/ssl/server.pass.key 2048
RUN openssl rsa -passin pass:NotSecure -in /etc/ssl/server.pass.key -out /etc/ssl/server.key
RUN rm /etc/ssl/server.pass.key

RUN openssl req -new -passout pass:NotSecure -key /etc/ssl/server.key -out /etc/ssl/server.csr \
    -subj "/C=HU/ST=Pest/L=Budapest/O=Symfony/CN=$NGINX_HOST" -config /etc/ssl/openssl.cnf
RUN openssl x509 -req -sha256 -days 365 -in /etc/ssl/server.csr -signkey /etc/ssl/server.key \
    -out /etc/ssl/server.crt -extensions v3_req -extfile /etc/ssl/openssl.cnf

#RUN openssl req -x509 -nodes -days 365 -subj "/C=CA/ST=QC/O=Company, Inc./CN=$NGINX_HOST" \
#    -addext "subjectAltName=DNS:$NGINX_HOST" \
#    -newkey rsa:2048 -keyout /etc/ssl/private/server.key \
#    -out /etc/ssl/certs/server.crt;

ADD ./conf/nginx.conf /etc/nginx/

RUN rm /var/log/nginx/access.log /var/log/nginx/error.log

#COPY ./entrypoint.sh /tmp/
#CMD /tmp/entrypoint.sh
