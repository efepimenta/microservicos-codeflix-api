FROM nginx:1.15.0-alpine

RUN rm /etc/nginx/conf.d/default.conf
COPY ./nginx.conf /etc/nginx/conf.d

#RUN apk update && apk add --no-cache bash shadow curl
#
#RUN usermod -u 1000 nginx && \
#    groupmod -g 1000 nginx
#
#RUN apk del bash shadow curl
