FROM alpine:3.19

LABEL maintainer="XMCVE"
LABEL description="XSS Vulnerability Labs - Educational Security Training Platform"
LABEL version="1.0"

# Install minimal PHP dependencies
# php-session: Required for cookie/session handling in XSS labs
RUN apk add --no-cache php php-cli php-session

# Create non-root user for security (even though this is a lab environment)
RUN addgroup -S xsslab && adduser -S xsslab -G xsslab

WORKDIR /var/www/html

# Copy application files
COPY --chown=xsslab:xsslab . /var/www/html

# Ensure data directory exists and is writable for stored XSS labs
RUN mkdir -p /var/www/html/data && \
    chown -R xsslab:xsslab /var/www/html/data && \
    chmod 755 /var/www/html/data

# Ensure upload directories for levels 35 and 38 are writable
RUN mkdir -p /var/www/html/level35/uploads /var/www/html/level38/uploads && \
    chown -R xsslab:xsslab /var/www/html/level35 /var/www/html/level38 && \
    chmod 755 /var/www/html/level35/uploads /var/www/html/level38/uploads

# Expose port 80 for web traffic
EXPOSE 80

# Health check for container orchestration
HEALTHCHECK --interval=30s --timeout=5s --start-period=5s --retries=3 \
    CMD wget -q --spider http://localhost:80/ || exit 1

# Switch to non-root user
USER xsslab

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/html"]
