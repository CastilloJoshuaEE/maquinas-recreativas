FROM nginx:1.24-alpine

RUN rm /etc/nginx/conf.d/default.conf

COPY default.conf /etc/nginx/conf.d/

EXPOSE 80 443

CMD ["nginx", "-g", "daemon off;"]