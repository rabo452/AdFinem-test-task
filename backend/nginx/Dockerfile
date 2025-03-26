# Use the official Nginx image from Docker Hub as the base image
FROM nginx:latest

# Copy the custom Nginx configuration file into the container
COPY nginx.conf /etc/nginx/nginx.conf

COPY error.html /var/www/html/

# Expose port 80 to be accessible from the host
EXPOSE 80

# Run Nginx in the foreground
CMD ["nginx", "-g", "daemon off;"]
