## Running with Docker

Build and run the container:

```bash
docker build -t my-php-app .
docker run -d -p 8080:80 --name my-php-app my-php-app
