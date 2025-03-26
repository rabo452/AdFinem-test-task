# Use the official MySQL image as the base image
FROM mysql:5.7

# Copy the init.sql script into the Docker container
COPY ./init.sql /docker-entrypoint-initdb.d/

# Expose the MySQL default port
EXPOSE 3306