# Deployment Guide

This guide covers deploying the ticketing system to production environments.

## Prerequisites

- PHP 8.3 or higher
- Composer
- Node.js and npm
- Web server (Apache/Nginx)
- Database (MySQL/PostgreSQL recommended for production)
- Mail server (if using email-to-ticket features)

## Environment Setup

### 1. Server Requirements

**Minimum System Requirements:**
- 2 CPU cores
- 4GB RAM
- 20GB disk space
- Linux (Ubuntu 20.04+ recommended)

**PHP Extensions:**
```bash
# Required extensions (PHP 8.3+)
php8.3-cli php8.3-fpm php8.3-mysql php8.3-pgsql php8.3-sqlite3
php8.3-zip php8.3-xml php8.3-mbstring php8.3-curl php8.3-gd
php8.3-json php8.3-bcmath php8.3-tokenizer php8.3-fileinfo

# For email parsing (optional)
php-mailparse
```

### 2. Web Server Configuration

**Nginx Configuration:**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/ticketing-system/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

**Apache Configuration (.htaccess):**
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## Application Deployment

### 1. Clone and Setup

```bash
# Clone repository
git clone <repository-url> /var/www/ticketing-system
cd /var/www/ticketing-system

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Set permissions
sudo chown -R www-data:www-data /var/www/ticketing-system
sudo chmod -R 755 /var/www/ticketing-system
sudo chmod -R 775 /var/www/ticketing-system/storage
sudo chmod -R 775 /var/www/ticketing-system/bootstrap/cache
```

### 2. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create storage link
php artisan storage:link
```

**Production .env Configuration:**
```bash
APP_NAME="Your Ticketing System"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://your-domain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticketing_system
DB_USERNAME=ticketing_user
DB_PASSWORD=secure_password

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server.com
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=support@your-domain.com
MAIL_FROM_NAME="Your Company Support"
MAIL_SUPPORT_DOMAIN=your-domain.com

# Cache Configuration
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Log Configuration
LOG_CHANNEL=daily
LOG_LEVEL=error
LOG_DAILY_DAYS=14
```

### 3. Database Setup

```bash
# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --force

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## Production Optimizations

### 1. Performance Optimizations

```bash
# Install OPcache (recommended)
sudo apt install php-opcache

# Configure OPcache in php.ini
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 2. Queue Worker Setup

**Supervisor Configuration (`/etc/supervisor/conf.d/ticketing-worker.conf`):**
```ini
[program:ticketing-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ticketing-system/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/ticketing-system/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Start supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start ticketing-worker:*
```

### 3. Cron Job Setup

Add to crontab (`sudo crontab -e`):
```bash
* * * * * cd /var/www/ticketing-system && php artisan schedule:run >> /dev/null 2>&1
```

## SSL/TLS Configuration

### 1. Let's Encrypt with Certbot

```bash
# Install certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d your-domain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 2. Nginx SSL Configuration

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    
    # Rest of configuration...
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}
```

## Monitoring and Logging

### 1. Log Management

```bash
# Configure logrotate
sudo nano /etc/logrotate.d/ticketing-system

# Content:
/var/www/ticketing-system/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

### 2. Health Checks

**Health Check Endpoint (routes/web.php):**
```php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'cache' => Cache::store()->getStore()->ping() ? 'connected' : 'disconnected',
    ]);
});
```

### 3. Error Tracking

Consider integrating with error tracking services:
- Sentry
- Bugsnag
- Rollbar

## Backup Strategy

### 1. Database Backups

```bash
#!/bin/bash
# /usr/local/bin/backup-ticketing-db.sh

BACKUP_DIR="/var/backups/ticketing-system"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="ticketing_system"
DB_USER="ticketing_user"
DB_PASSWORD="secure_password"

mkdir -p $BACKUP_DIR

# Create backup
mysqldump -u $DB_USER -p$DB_PASSWORD $DB_NAME | gzip > $BACKUP_DIR/ticketing_db_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -name "ticketing_db_*.sql.gz" -mtime +30 -delete

# Crontab entry: 0 2 * * * /usr/local/bin/backup-ticketing-db.sh
```

### 2. File Backups

```bash
#!/bin/bash
# /usr/local/bin/backup-ticketing-files.sh

BACKUP_DIR="/var/backups/ticketing-system"
APP_DIR="/var/www/ticketing-system"
DATE=$(date +%Y%m%d_%H%M%S)

# Backup storage directory (uploads, logs, etc.)
tar -czf $BACKUP_DIR/ticketing_storage_$DATE.tar.gz -C $APP_DIR storage

# Backup .env file
cp $APP_DIR/.env $BACKUP_DIR/env_$DATE.backup

# Keep only last 30 days
find $BACKUP_DIR -name "ticketing_storage_*.tar.gz" -mtime +30 -delete
find $BACKUP_DIR -name "env_*.backup" -mtime +30 -delete
```

## Security Hardening

### 1. File Permissions

```bash
# Set secure permissions
sudo chown -R www-data:www-data /var/www/ticketing-system
sudo find /var/www/ticketing-system -type f -exec chmod 644 {} \;
sudo find /var/www/ticketing-system -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/ticketing-system/storage
sudo chmod -R 775 /var/www/ticketing-system/bootstrap/cache
sudo chmod 600 /var/www/ticketing-system/.env
```

### 2. Firewall Configuration

```bash
# UFW (Ubuntu Firewall)
sudo ufw enable
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw allow 25/tcp  # SMTP (if using email-to-ticket)
```

### 3. Additional Security Headers

**Nginx Security Headers:**
```nginx
# Add to server block
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
```

## Maintenance

### 1. Regular Updates

```bash
# Update dependencies
composer update --no-dev --optimize-autoloader
npm update && npm run build

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Database Maintenance

```bash
# Optimize database tables
php artisan db:optimize

# Clean up old logs
php artisan telescope:prune --hours=168  # Keep 1 week
```

## Troubleshooting

### Common Issues

**Permission Errors:**
```bash
sudo chown -R www-data:www-data /var/www/ticketing-system/storage
sudo chmod -R 775 /var/www/ticketing-system/storage
```

**Cache Issues:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

**Queue Not Processing:**
```bash
sudo supervisorctl restart ticketing-worker:*
php artisan queue:restart
```

**Database Connection Issues:**
- Check database credentials in .env
- Verify database server is running
- Check firewall rules for database port

### Performance Issues

**High Memory Usage:**
- Increase PHP memory limit
- Optimize database queries
- Enable OPcache
- Use Redis for caching

**Slow Response Times:**
- Enable caching (config, routes, views)
- Optimize database indexes
- Use CDN for static assets
- Enable gzip compression

This deployment guide provides a comprehensive foundation for running the ticketing system in production environments securely and efficiently.