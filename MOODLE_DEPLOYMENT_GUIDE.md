# Moodle Komodo Stack Deployment Guide

## Overview
This guide covers the deployment of Moodle (v5.2) learning platform to Komodo as a managed stack.

## Files Included

1. **moodle.Dockerfile** - Multi-stage Docker image for Moodle
2. **docker-compose.yml** - Complete stack definition with Moodle + PostgreSQL
3. **config.php** - Environment-aware Moodle configuration
4. **register-moodle.py** - Python script for Komodo API registration

## Prerequisites

- Komodo running on `192.168.200.40:9120`
- Docker and Docker Compose installed
- Moodle repository cloned at `/etc/komodo/repos/moodle`
- Komodo API credentials (API_KEY and API_SECRET)

## Installation Steps

### Step 1: Copy Stack Files

```bash
# Create moodle stack directory
mkdir -p /etc/komodo/stacks/moodle
mkdir -p /etc/komodo/stacks/moodle/config

# Copy docker-compose.yml
cp /root/komodo/stacks/moodle/docker-compose.yml /etc/komodo/stacks/moodle/

# Copy config.php
cp /root/komodo/stacks/moodle/config.php /etc/komodo/stacks/moodle/

# Copy Dockerfile
cp /root/komodo/moodle.Dockerfile /etc/komodo/stacks/moodle/
```

### Step 2: Configure Environment Variables

Create `.env` file in `/etc/komodo/stacks/moodle/`:

```bash
cat > /etc/komodo/stacks/moodle/.env << 'EOF'
# Moodle Configuration
MOODLE_URL=https://moodle-stg.ligjamaica.com
MOODLE_DATAROOT=/var/www/moodledata
MOODLE_ADMIN=admin

# Database Configuration
DB_TYPE=pgsql
DB_HOST=moodle-db
DB_PORT=5432
DB_NAME=moodle
DB_USER=moodle
DB_PASSWORD=your_secure_password_here

# SMTP Configuration (Optional)
SMTP_HOST=smtp.ligjamaica.com
SMTP_USER=your_smtp_user
SMTP_PASSWORD=your_smtp_password
SMTP_SECURE=tls
NOREPLY_ADDRESS=noreply@ligjamaica.com

# Redis Cache (Optional)
USE_REDIS=false
REDIS_HOST=moodle-redis
REDIS_PORT=6379
EOF
```

### Step 3: Build the Docker Image

```bash
cd /etc/komodo/repos/moodle
docker build -f /root/komodo/moodle.Dockerfile -t moodle:latest .
```

Or let Komodo build it via docker-compose:

```bash
cd /etc/komodo/stacks/moodle
docker-compose build
```

### Step 4: Configure Komodo Registration Script

Edit `/root/komodo/register-moodle.py` and update:

```python
API_KEY = "your_komodo_api_key"
API_SECRET = "your_komodo_api_secret"
```

Get these from your Komodo admin console.

### Step 5: Deploy Stack

**Option A: Using the Python Script**

```bash
cd /root/komodo
python3 register-moodle.py
```

**Option B: Manual Docker Compose Deployment**

```bash
cd /etc/komodo/stacks/moodle
docker-compose up -d
```

**Option C: Using Komodo CLI**

```bash
python3 komodo-cli.py deploy moodle
```

### Step 6: Verify Deployment

```bash
# Check container status
docker ps | grep moodle

# View logs
docker logs moodle

# Test health endpoint
curl -f http://localhost:8080/index.php

# Check database connectivity
docker exec moodle pg_isready -h moodle-db -U moodle
```

## Initial Configuration

### Access Moodle

Navigate to: `https://moodle-stg.ligjamaica.com` (or your configured URL)

### First-Time Setup

1. **Admin User Creation**
   - Username: admin
   - Password: Create a strong password
   - Email: admin@yourdomain.com

2. **Site Configuration**
   - Site name: Your Learning Platform
   - Site description: Platform description
   - Front page settings: Customize as needed

3. **Database Setup**
   - The installer should auto-detect PostgreSQL
   - Verify credentials match `.env` file
   - Allow database tables to be created

### Database Connection

If the installer doesn't auto-detect the database:

```php
// Manual database configuration in config.php
$CFG->dbtype    = 'pgsql';
$CFG->dbhost    = 'moodle-db';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'moodle';
$CFG->dbpass    = 'your_password';
```

## Stack Components

### Services

1. **moodle** (PHP-FPM + Nginx)
   - Port: 8080
   - Handles all Moodle application logic
   - Health checks every 30 seconds

2. **moodle-db** (PostgreSQL 16)
   - Persistent data storage
   - Automatic backups recommended

### Volumes

- `moodle-data`: Moodle data directory (`/var/www/moodledata`)
- `moodle-db-data`: PostgreSQL data directory

## Common Operations

### Start Stack
```bash
docker-compose -f /etc/komodo/stacks/moodle/docker-compose.yml up -d
```

### Stop Stack
```bash
docker-compose -f /etc/komodo/stacks/moodle/docker-compose.yml down
```

### View Logs
```bash
docker logs -f moodle
docker logs -f moodle-db
```

### Access Database CLI
```bash
docker exec -it moodle-db psql -U moodle -d moodle
```

### Create Database Backup
```bash
docker exec moodle-db pg_dump -U moodle -d moodle > moodle_backup_$(date +%Y%m%d_%H%M%S).sql
```

### Restore Database
```bash
docker exec -i moodle-db psql -U moodle -d moodle < moodle_backup_YYYYMMDD_HHMMSS.sql
```

## Performance Tuning

### PHP Configuration
Edit `/root/komodo/moodle.Dockerfile` to adjust:

```dockerfile
RUN echo "memory_limit = 1024M" >> /usr/local/etc/php/conf.d/moodle.ini
RUN echo "upload_max_filesize = 500M" >> /usr/local/etc/php/conf.d/moodle.ini
RUN echo "max_execution_time = 600" >> /usr/local/etc/php/conf.d/moodle.ini
```

### Database Optimization
```sql
-- Connect to database
docker exec -it moodle-db psql -U moodle -d moodle

-- Check index usage
SELECT schemaname, tablename, indexname FROM pg_indexes WHERE schemaname != 'pg_catalog';

-- Vacuum and analyze
VACUUM ANALYZE;
```

### Enable Caching
Uncomment Redis configuration in `/etc/komodo/stacks/moodle/config.php`:

```php
$CFG->USE_REDIS = 'true';
$CFG->cache_redis_host = 'moodle-redis';
```

## Security Considerations

1. **Change Default Passwords**
   - Update `MOODLE_DB_PASSWORD` in `.env`
   - Change admin password after first login

2. **Enable HTTPS**
   - Configure SSL certificates via Komodo/nginx
   - Update `$CFG->wwwroot` to use `https://`
   - Set `$CFG->cookiesecure = true`

3. **Firewall Rules**
   - Restrict database port (5432) to internal traffic only
   - Use API authentication for Komodo registration

4. **Regular Backups**
   - Backup database daily: `/etc/komodo/stacks/moodle/backups/`
   - Backup moodledata volume regularly

## Troubleshooting

### Container Won't Start
```bash
# Check Docker logs
docker logs moodle

# Check PHP errors
docker exec moodle tail -f /var/log/php-fpm.log

# Verify file permissions
docker exec moodle ls -la /app/config.php
```

### Database Connection Errors
```bash
# Test database connectivity
docker exec moodle ping moodle-db
docker exec moodle psql -h moodle-db -U moodle -d moodle -c "SELECT 1;"
```

### 502 Bad Gateway
```bash
# Restart PHP-FPM and Nginx
docker exec moodle supervisorctl restart php-fpm
docker exec moodle supervisorctl restart nginx
```

### Storage Issues
```bash
# Check available space
df -h

# Check moodledata directory size
du -sh /var/www/moodledata
```

## Maintenance

### Regular Tasks

- **Daily**: Monitor logs, check storage
- **Weekly**: Verify backups, check performance metrics
- **Monthly**: Review user activity, clean cache, update plugins
- **Quarterly**: Test disaster recovery, update PHP packages

### Update Moodle

```bash
cd /etc/komodo/repos/moodle
git pull origin main
cd /etc/komodo/stacks/moodle
docker-compose up --build -d
```

## Support & Documentation

- **Moodle Official Docs**: https://docs.moodle.org/
- **Komodo Documentation**: Check local Komodo instance
- **LIG Jamaica Infrastructure Team**: For infrastructure issues

## License

Moodle is licensed under GNU General Public License v3.0 or later (GPL-3.0-or-later)
