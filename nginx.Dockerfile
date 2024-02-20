FROM nginxinc/nginx-unprivileged as prod

COPY docker/nginx/conf.d/default.conf /etc/nginx/templates/default.conf.template
COPY bee-health-data-portal/public/ /usr/share/nginx/html/