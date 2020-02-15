/bin/bash
  "envsubst '$$NGINX_HOST'
  < /etc/nginx/conf.d/default_template.cnf
  > /etc/nginx/conf.d/default.conf
  && exec nginx -g 'daemon off;'"
