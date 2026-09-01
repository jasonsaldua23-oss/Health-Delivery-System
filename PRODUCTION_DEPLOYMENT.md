# Health Delivery System - Production Deployment & Operations Guide

This guide provides instructions for deploying and running the **Bacolod City Health Delivery System** in a secure, high-performance production environment.

---

## 1. System Requirements

| Component | Minimum Requirement | Recommended |
| :--- | :--- | :--- |
| **Operating System** | Linux (Ubuntu 22.04 LTS / Debian 12 / RHEL 9) or Windows Server | Ubuntu 24.04 LTS |
| **Web Server** | Apache 2.4+ (with `mod_rewrite`, `mod_headers`, `mod_deflate`) or Nginx 1.20+ | Apache 2.4+ or Nginx with PHP-FPM |
| **PHP Version** | PHP 8.1+ | PHP 8.2+ or 8.3 |
| **PHP Extensions** | `mysqli`, `curl`, `json`, `mbstring`, `openssl`, `session`, `fileinfo` | Enabled and verified |
| **Database** | MySQL 8.0+ or MariaDB 10.4+ | MySQL 8.0+ / MariaDB 10.11 LTS |
| **SSL / HTTPS** | TLS 1.2 / TLS 1.3 Certificate (Let's Encrypt / DigiCert) | Required for production |

---

## 2. Step-by-Step Deployment Procedure

### Step 1: Clone or Copy Application Files
Place the repository into your web root (e.g. `/var/www/health-delivery-system` or `C:\xampp\htdocs\Health-Delivery-System-Latest`):
```bash
cd /var/www/health-delivery-system
```

### Step 2: Configure Environment Variables
Copy `.env.example` to `.env` and configure your production credentials:
```bash
cp .env.example .env
```
Edit `.env` with your secure credentials:
```ini
APP_ENV=production
APP_NAME="Health Delivery System - Bacolod"
APP_URL=https://health.bacolodcity.gov.ph

# Database Connection
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=health_app_user
DB_PASS=YourStrongProductionPasswordHere!
DB_NAME=health_delivery_system

# Brevo SMS Gateway Key
BREVO_API_KEY=your_brevo_api_key_here
```

### Step 3: Run Database Migrations
Execute the standalone CLI migration runner:
```bash
php shared/migrate.php
```
This automatically:
- Creates the `health_delivery_system` database with `utf8mb4` encoding if not present.
- Builds all 14 core tables with correct constraints and engine definitions.
- Adds performance indexes (`idx_appt_date_status`, `idx_appt_station_date`, `idx_appt_patient`, etc.).
- Seeds initial accounts (admin, staff for 15 barangays, sample patient records, default services).

### Step 4: Configure File Permissions (Linux/Unix)
Ensure that the web server user (e.g., `www-data`) owns the upload and log directories:
```bash
# Set directory permissions
sudo chown -R www-data:www-data /var/www/health-delivery-system
sudo find /var/www/health-delivery-system -type d -exec chmod 755 {} \;
sudo find /var/www/health-delivery-system -type f -exec chmod 644 {} \;

# Grant write permissions for upload and logs
sudo chmod -R 775 /var/www/health-delivery-system/Patients/uploads
sudo chmod -R 775 /var/www/health-delivery-system/logs
```

---

## 3. Web Server Configurations

### Option A: Apache (Recommended)
Make sure the required Apache modules are enabled:
```bash
sudo a2enmod rewrite headers deflate
sudo systemctl restart apache2
```
Ensure `AllowOverride All` is configured in your VirtualHost so the root `.htaccess` and `Patients/uploads/.htaccess` take effect:
```apache
<VirtualHost *:80>
    ServerName health.bacolodcity.gov.ph
    Redirect permanent / https://health.bacolodcity.gov.ph/
</VirtualHost>

<VirtualHost *:443>
    ServerName health.bacolodcity.gov.ph
    DocumentRoot /var/www/health-delivery-system

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/health.bacolodcity.gov.ph/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/health.bacolodcity.gov.ph/privkey.pem

    <Directory /var/www/health-delivery-system>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/health_error.log
    CustomLog ${APACHE_LOG_DIR}/health_access.log combined
</VirtualHost>
```

### Option B: Nginx + PHP-FPM
If using Nginx, configure the server block as follows:
```nginx
server {
    listen 80;
    server_name health.bacolodcity.gov.ph;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name health.bacolodcity.gov.ph;
    root /var/www/health-delivery-system;
    index index.php index.html;

    ssl_certificate /etc/letsencrypt/live/health.bacolodcity.gov.ph/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/health.bacolodcity.gov.ph/privkey.pem;

    # Security Headers
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Deny direct access to sensitive files
    location ~* \.(env|sql|log|bat|sh|md|json|lock)$ {
        deny all;
    }

    # Deny direct access to hidden / dot files
    location ~ /\. {
        deny all;
    }

    # Prevent PHP script execution in uploads directory
    location ~ ^/Patients/uploads/.*\.php$ {
        deny all;
    }

    # Prevent access to internal / tooling directories
    location ~ ^/(cypress|tests|node_modules|logs|\.git) {
        deny all;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 4. Production Security Checklist

- [x] **Environment Separation**: `APP_ENV=production` hides debug traces and routes errors to `logs/php_errors.log`.
- [x] **Secrets Management**: Database passwords and Brevo SMS API keys are stored in `.env` (excluded from git).
- [x] **Password Security**: All authentications enforce standard `password_verify()` with bcrypt hashing; backdoor passwords removed.
- [x] **Session Fixation Defense**: `session_regenerate_id(true)` called upon every login.
- [x] **Cookie Security**: `HttpOnly`, `SameSite=Lax`, and `Secure` (over HTTPS) enabled.
- [x] **CSRF Protection**: Form submissions validate anti-CSRF tokens.
- [x] **Upload Protection**: `uploads/.htaccess` blocks script execution.
- [x] **Fast Database Connection**: Expensive schema migrations decoupled from live user requests into `php shared/migrate.php`.
- [x] **Error Pages**: Custom 403, 404, and 500 pages prevent leaking server topology.

---

## 5. Automated Tasks & Cron Jobs (Recommended)

Set up a daily cronjob to clean up old logs and archive unattended appointments:
```bash
# Open crontab for editing
crontab -e

# Add automated maintenance tasks (runs daily at 2:00 AM)
0 2 * * * php /var/www/health-delivery-system/shared/migrate.php > /dev/null 2>&1
```
