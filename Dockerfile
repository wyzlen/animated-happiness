FROM nginx:stable-alpine
COPY . /usr/share/nginx/php
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
