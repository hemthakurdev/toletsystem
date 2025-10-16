# SaleMitra Deployment Guide

## 🚀 Production Deployment Checklist

### 1. Environment Configuration

#### Database Setup
```env
DB_CONNECTION=mysql
DB_HOST=your_database_host
DB_PORT=3306
DB_DATABASE=salemitra_production
DB_USERNAME=your_db_username
DB_PASSWORD=your_secure_password
```

#### Application Settings
```env
APP_NAME="SaleMitra"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=base64:your_generated_key_here
```

#### Mail Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email@domain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="SaleMitra"
```

#### File Storage (AWS S3)
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_aws_access_key
AWS_SECRET_ACCESS_KEY=your_aws_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-s3-bucket-name
```

#### Razorpay Configuration
```env
RAZORPAY_KEY_ID=rzp_live_your_live_key_id
RAZORPAY_KEY_SECRET=your_live_key_secret
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret
```

### 2. Server Requirements

#### Minimum Requirements
- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or higher
- **Redis**: 6.0 or higher (for caching)
- **Nginx/Apache**: Latest stable version
- **SSL Certificate**: Required for production

#### Recommended Server Specs
- **CPU**: 2+ cores
- **RAM**: 4GB+ (8GB recommended)
- **Storage**: 50GB+ SSD
- **Bandwidth**: Unlimited

### 3. Deployment Steps

#### Step 1: Server Setup
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-redis

# Install MySQL
sudo apt install mysql-server

# Install Nginx
sudo apt install nginx

# Install Redis
sudo apt install redis-server

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

#### Step 2: Application Deployment
```bash
# Clone repository
git clone https://github.com/yourusername/salemitra.git
cd salemitra

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Copy environment file
cp .env.example .env
# Edit .env with production values
nano .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Seed database (optional)
php artisan db:seed --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Step 3: Nginx Configuration
```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/salemitra/public;

    # SSL Configuration
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### Step 4: SSL Certificate (Let's Encrypt)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 4. Performance Optimization

#### Redis Configuration
```bash
# Edit Redis config
sudo nano /etc/redis/redis.conf

# Set memory limit
maxmemory 256mb
maxmemory-policy allkeys-lru

# Restart Redis
sudo systemctl restart redis-server
```

#### PHP-FPM Optimization
```bash
# Edit PHP-FPM config
sudo nano /etc/php/8.2/fpm/pool.d/www.conf

# Optimize settings
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

#### Laravel Optimization
```bash
# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize Composer autoloader
composer dump-autoload --optimize
```

### 5. Monitoring & Logging

#### Log Rotation
```bash
# Create logrotate config
sudo nano /etc/logrotate.d/salemitra

# Add:
/var/www/salemitra/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 644 www-data www-data
}
```

#### System Monitoring
```bash
# Install monitoring tools
sudo apt install htop iotop nethogs

# Monitor logs
tail -f /var/www/salemitra/storage/logs/laravel.log
```

### 6. Backup Strategy

#### Database Backup
```bash
# Create backup script
sudo nano /usr/local/bin/backup-salemitra.sh

#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/salemitra"
DB_NAME="salemitra_production"

mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u root -p$DB_PASSWORD $DB_NAME > $BACKUP_DIR/db_backup_$DATE.sql

# Application backup
tar -czf $BACKUP_DIR/app_backup_$DATE.tar.gz /var/www/salemitra

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

# Make executable
sudo chmod +x /usr/local/bin/backup-salemitra.sh

# Add to crontab
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-salemitra.sh
```

### 7. Security Checklist

- [ ] Change default database passwords
- [ ] Set up firewall (UFW)
- [ ] Configure fail2ban
- [ ] Enable SSL/TLS
- [ ] Set up regular security updates
- [ ] Configure proper file permissions
- [ ] Enable two-factor authentication
- [ ] Set up intrusion detection

### 8. Post-Deployment Testing

#### Health Checks
```bash
# Test application
curl -I https://yourdomain.com

# Test API
curl -I https://yourdomain.com/api/v1/properties

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Test Redis connection
php artisan tinker
>>> Redis::ping();
```

#### Performance Testing
```bash
# Install Apache Bench
sudo apt install apache2-utils

# Test performance
ab -n 1000 -c 10 https://yourdomain.com/
```

### 9. Maintenance

#### Regular Tasks
- [ ] Monitor server resources
- [ ] Check application logs
- [ ] Update dependencies
- [ ] Backup verification
- [ ] Security updates
- [ ] Performance optimization

#### Laravel Maintenance
```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Re-cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 10. Scaling Considerations

#### Horizontal Scaling
- Load balancer setup
- Database replication
- Redis clustering
- CDN integration

#### Vertical Scaling
- Increase server resources
- Optimize database queries
- Implement caching strategies
- Use queue workers

## 🎯 Go-Live Checklist

- [ ] Domain configured
- [ ] SSL certificate installed
- [ ] Environment variables set
- [ ] Database migrated
- [ ] Assets built
- [ ] Caches cleared
- [ ] Monitoring setup
- [ ] Backup configured
- [ ] Security hardened
- [ ] Performance tested
- [ ] Documentation updated

## 📞 Support

For deployment support:
- Check Laravel documentation
- Review server logs
- Monitor application performance
- Contact system administrator

---

**Ready to deploy SaleMitra to production! 🚀**
