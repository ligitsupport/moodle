# Moodle Komodo Stack - Complete Package

## Summary
This package contains everything needed to deploy Moodle 5.2 learning platform to Komodo as a managed Docker stack.

## Files Included

### Core Deployment Files

| File | Location | Purpose |
|------|----------|---------|
| `moodle.Dockerfile` | `/root/komodo/` | Multi-stage Docker image for Moodle with PHP 8.3, Nginx, and all required extensions |
| `docker-compose.yml` | `/root/komodo/stacks/moodle/` | Complete stack definition with Moodle app and PostgreSQL database |
| `config.php` | `/root/komodo/stacks/moodle/` | Environment-aware Moodle configuration file |
| `register-moodle.py` | `/root/komodo/` | Python script to register and deploy stack via Komodo API |

### Documentation

| File | Location | Purpose |
|------|----------|---------|
| `MOODLE_DEPLOYMENT_GUIDE.md` | `/root/komodo/` | Complete deployment guide with step-by-step instructions |
| `MOODLE_CHEATSHEET.sh` | `/root/komodo/` | Quick reference for common Moodle operations |
| `MOODLE_STACK_PACKAGE.md` | `/root/komodo/` | This file |

## Quick Start

### 1. Prerequisites Check
```bash
# Verify repository
ls -la /etc/komodo/repos/moodle/

# Verify Komodo is running
curl -I http://192.168.200.40:9120/

# Verify Docker
docker --version && docker-compose --version
```

### 2. Build Docker Image
```bash
cd /etc/komodo/repos/moodle
docker build -f /root/komodo/moodle.Dockerfile -t moodle:latest .
```

### 3. Configure Stack
```bash
# Create environment file
cat > /etc/komodo/stacks/moodle/.env << 'EOF'
MOODLE_URL=https://moodle-stg.ligjamaica.com
DB_PASSWORD=your_secure_password
EOF
```

### 4. Deploy
**Option A: Docker Compose**
```bash
cd /etc/komodo/stacks/moodle
docker-compose up -d
```

**Option B: Komodo API**
```bash
# Update API credentials in register-moodle.py first
python3 /root/komodo/register-moodle.py
```

### 5. Verify
```bash
docker ps | grep moodle
curl -f http://localhost:8080/index.php && echo "✅ OK" || echo "❌ Failed"
```

## Stack Architecture

```
┌─────────────────────────────────────────────┐
│           Komodo Container Platform         │
├─────────────────────────────────────────────┤
│                                             │
│  ┌──────────────────────┐                   │
│  │   moodle (Port 8080) │                   │
│  ├──────────────────────┤                   │
│  │ - PHP-FPM            │                   │
│  │ - Nginx              │                   │
│  │ - Supervisor         │                   │
│  │ - All PHP extensions │                   │
│  └──────────────────────┘                   │
│         ↓ (TCP 5432)                        │
│  ┌──────────────────────┐                   │
│  │   moodle-db          │                   │
│  ├──────────────────────┤                   │
│  │ - PostgreSQL 16      │                   │
│  │ - Health checks      │                   │
│  └──────────────────────┘                   │
│                                             │
│  Volumes:                                   │
│  - moodle-data (persistent)                 │
│  - moodle-db-data (persistent)              │
│                                             │
└─────────────────────────────────────────────┘
```

## Configuration Files

### Environment Variables (`.env`)
```bash
MOODLE_URL=https://moodle-stg.ligjamaica.com
DB_TYPE=pgsql
DB_HOST=moodle-db
DB_PORT=5432
DB_NAME=moodle
DB_USER=moodle
DB_PASSWORD=secure_password_here
SMTP_HOST=smtp.ligjamaica.com
NOREPLY_ADDRESS=noreply@ligjamaica.com
```

### Moodle Configuration (`config.php`)
- Database connection
- Session management
- Email settings
- Caching (Redis support)
- Performance optimization
- Security settings
- Logging configuration

## Common Tasks

### View Deployment Progress
```bash
docker logs -f moodle
docker logs -f moodle-db
```

### Access Database
```bash
docker exec -it moodle-db psql -U moodle -d moodle
```

### Backup Database
```bash
docker exec moodle-db pg_dump -U moodle -d moodle > backup.sql
```

### Restart Services
```bash
docker-compose -f /etc/komodo/stacks/moodle/docker-compose.yml restart moodle
```

### View Quick Reference
```bash
bash /root/komodo/MOODLE_CHEATSHEET.sh
```

## Accessing Moodle

Once deployed:

- **URL**: https://moodle-stg.ligjamaica.com (or configured URL)
- **Local Access**: http://localhost:8080
- **Admin Panel**: /admin
- **Database**: moodle-db:5432

## System Requirements

- **CPU**: Minimum 2 cores (4 recommended)
- **RAM**: Minimum 2GB (4GB recommended)
- **Disk**: Minimum 20GB (100GB recommended for production)
- **Database**: PostgreSQL 16
- **PHP**: 8.3 with required extensions

## Features Included

✅ Multi-stage Docker build (optimized image size)  
✅ PHP-FPM + Nginx (production-grade web server)  
✅ PostgreSQL 16 (robust database)  
✅ Supervisor (process management)  
✅ Health checks (automatic monitoring)  
✅ Environment-aware configuration  
✅ Persistent volumes (data preservation)  
✅ Komodo API integration  
✅ SMTP support (email notifications)  
✅ Redis caching support  
✅ Security optimizations  

## Troubleshooting

For detailed troubleshooting guide, see: [MOODLE_DEPLOYMENT_GUIDE.md](MOODLE_DEPLOYMENT_GUIDE.md)

Common issues:
- **Container won't start**: Check Docker logs `docker logs moodle`
- **Database connection fails**: Verify .env file and run `docker-compose ps`
- **502 Bad Gateway**: Restart PHP-FPM `docker exec moodle supervisorctl restart php-fpm`
- **High memory usage**: Adjust PHP config in Dockerfile and rebuild

## Support Resources

- [Moodle Official Documentation](https://docs.moodle.org/)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [Docker Documentation](https://docs.docker.com/)
- [Komodo Stack Management](http://192.168.200.40:9120/)

## Next Steps

1. ✅ Review [MOODLE_DEPLOYMENT_GUIDE.md](MOODLE_DEPLOYMENT_GUIDE.md) for detailed instructions
2. ✅ Build Docker image using moodle.Dockerfile
3. ✅ Configure environment variables in `.env`
4. ✅ Deploy using docker-compose or register-moodle.py
5. ✅ Access Moodle web interface for initial setup
6. ✅ Create admin user and configure site
7. ✅ Set up backup procedures
8. ✅ Monitor logs and performance

## Version Information

- **Moodle**: 5.2 (latest stable)
- **PHP**: 8.3
- **PostgreSQL**: 16-alpine
- **Docker Base**: PHP:8.3-FPM-Alpine
- **Nginx**: Latest Alpine

## License

Moodle is licensed under **GNU General Public License v3.0 or later (GPL-3.0-or-later)**

---

**Created**: April 23, 2026  
**For**: LIG Jamaica Infrastructure Team  
**Environment**: Komodo Stack Platform
