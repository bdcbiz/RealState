# Real Estate API - Production Deployment Guide

## Prerequisites

- PHP 8.2 or higher
- MySQL 8.0 or higher
- Composer
- Git
- Web server (Nginx/Apache)

## Deployment Steps

### 1. Clone the Repository

```bash
cd /var/www
git clone https://github.com/bdcbiz/RealState.git realestate
cd realestate
```

### 2. Install Dependencies

```bash
composer install --optimize-autoloader --no-dev
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file and configure:

```env
APP_NAME="Real Estate API"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://aqar.bdcbiz.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_secure_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### 4. Database Setup

```bash
# Create fresh database with migrations
php artisan migrate:fresh --seed

# OR if you have existing data, run migrations only
php artisan migrate --force
```

### 5. Set Permissions

```bash
chmod -R 755 /var/www/realestate
chmod -R 775 /var/www/realestate/storage
chmod -R 775 /var/www/realestate/bootstrap/cache
chown -R www-data:www-data /var/www/realestate
```

### 6. Optimize for Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 7. Configure Web Server

#### Nginx Configuration

```nginx
server {
    listen 443 ssl http2;
    server_name aqar.bdcbiz.com;
    root /var/www/realestate/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:18000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # SSL certificates
    ssl_certificate /etc/letsencrypt/live/aqar.bdcbiz.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/aqar.bdcbiz.com/privkey.pem;
}
```

### 8. Test Installation

```bash
# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Test API endpoint
curl https://aqar.bdcbiz.com/api/companies
```

## Default Users

After running the seeder, the following test accounts are available:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@realestate.com | password |
| Company Admin | company-admin@bdcbiz.com | password |
| Buyer | buyer@test.com | password |
| Seller | seller@test.com | password |

**⚠️ IMPORTANT:** Change all default passwords immediately in production!

## Database Structure

The database includes the following main tables:

- **users** - User accounts with role-based access
- **companies** - Real estate companies
- **compounds** - Property compounds/projects
- **units** - Individual units within compounds
- **sales** - Sales and promotions
- **personal_access_tokens** - API authentication (Sanctum)

## Admin Panel Access

The Filament admin panel is available at:
- URL: https://aqar.bdcbiz.com/admin
- Login with admin account credentials

## API Documentation

API endpoints are documented in:
- `ALL-API-ENDPOINTS.txt`
- `COMPLETE_API_DOCUMENTATION.md`

## Troubleshooting

### 403 Forbidden Error

If you get 403 error on `/admin`:

1. Check User model has `canAccessPanel()` method
2. Clear cache: `php artisan cache:clear`
3. Check Nginx/Apache configuration
4. Verify file permissions

### Migration Issues

```bash
# Fresh install (WARNING: deletes all data)
php artisan migrate:fresh --seed

# Rollback and re-migrate
php artisan migrate:rollback
php artisan migrate
```

### Cache Issues

```bash
# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear
```

## Updating the Application

```bash
cd /var/www/realestate
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Security Checklist

- [ ] Change all default passwords
- [ ] Set `APP_DEBUG=false` in production
- [ ] Configure proper file permissions
- [ ] Enable HTTPS with valid SSL certificate
- [ ] Configure firewall rules
- [ ] Set up regular database backups
- [ ] Enable Cloudflare WAF if needed
- [ ] Review and restrict API rate limiting

## Support

For issues or questions, please contact the development team or create an issue on GitHub.
