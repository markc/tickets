---
title: "Quick Start Guide"
description: "Get TIKM running in 5 minutes"
order: 1
category: "user"
version: "1.0"
last_updated: "2025-06-10"
---

# Quick Start Guide

Get TIKM running locally in under 5 minutes with this step-by-step guide.

## Prerequisites

Before you begin, ensure you have:

- **PHP 8.3+** installed
- **Composer** for dependency management
- **Node.js 18+** and npm

## Installation

### 1. Clone and Install Dependencies

```bash
# Clone the repository
git clone https://github.com/yourproject/tikm.git
cd tikm

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 2. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Database Setup

```php
<?php
// Run migrations and seeders
use Illuminate\Support\Facades\Artisan;

Artisan::call('migrate');
Artisan::call('db:seed');
```

### 4. Start Development Server

```bash
# Start all development services
composer dev

# Or start individual services
php artisan serve
npm run dev
php artisan queue:listen
```

## Testing the Installation

| Service | URL | Description |
|---------|-----|-------------|
| **Main App** | http://localhost:8000 | Customer interface |
| **Admin Panel** | http://localhost:8000/admin | Agent/Admin interface |
| **API** | http://localhost:8000/api | REST API endpoints |

### Default Login Credentials

> **Note**: Change these credentials in production!

- **Admin**: admin@example.com / password
- **Agent**: agent@example.com / password  
- **Customer**: customer@example.com / password

## Next Steps

- [ ] Configure email settings for ticket notifications
- [ ] Set up your first office/department
- [ ] Create custom ticket statuses and priorities
- [ ] Import existing FAQs
- **Git** for version control

## Installation Steps

### 1. Clone the Repository

```bash
git clone <repository-url> tikm
cd tikm
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup

```bash
# Run database migrations
php artisan migrate

# Seed with test data
php artisan db:seed
```

### 5. Build Frontend Assets

```bash
# Build for development with hot reload
npm run dev
```

### 6. Start Development Server

```bash
# Start all services (recommended)
composer dev
```

This command starts:
- PHP development server (port 8000)
- Queue listener for background jobs
- Log viewer (Laravel Pail)
- Vite development server with hot reload

## Access Points

Once running, you can access:

- **Customer Portal**: http://127.0.0.1:8000
- **Admin Panel**: http://127.0.0.1:8000/admin
- **Login Page**: http://127.0.0.1:8000/login
- **FAQ Section**: http://127.0.0.1:8000/faq

## Test Accounts

Use these pre-configured accounts:

| Role | Email | Password | Access |
|------|-------|----------|--------|
| **Admin** | admin@example.com | password | Full system access |
| **Agent** | agent@example.com | password | Ticket management |
| **Customer** | customer@example.com | password | Create and view tickets |

## First Steps

### As a Customer
1. Login with customer credentials
2. Visit the dashboard to see your ticket overview
3. Click "Create New Ticket" to submit your first support request
4. Upload attachments and set priority
5. Track your ticket status and replies

### As an Agent
1. Login with agent credentials
2. Access the admin panel at `/admin`
3. View assigned tickets in your dashboard
4. Respond to customer inquiries
5. Update ticket status and priorities

### As an Administrator
1. Login with admin credentials
2. Access the admin panel at `/admin`
3. Configure offices (departments)
4. Manage users and assign agent roles
5. View system analytics and reports

## Development Commands

### Useful Commands

```bash
# Start development environment
composer dev                    # All services
php artisan serve              # PHP server only
npm run dev                    # Frontend with hot reload

# Database operations
php artisan migrate            # Run migrations
php artisan db:seed           # Run seeders
php artisan migrate:fresh --seed # Fresh database

# Testing
composer test                  # Run all tests
./vendor/bin/pint             # Fix code style

# Queue management
php artisan queue:listen       # Process background jobs
php artisan queue:work        # Process jobs (production)

# Real-time features
php artisan reverb:start      # WebSocket server
```

## Troubleshooting

### Common Issues

**"Class not found" errors**
```bash
composer dump-autoload
```

**Permission errors**
```bash
chmod -R 755 storage bootstrap/cache
```

**Frontend not loading**
```bash
npm run build  # Build assets
php artisan view:clear  # Clear view cache
```

**Email not working**
- Check `.env` mail configuration
- Ensure queue worker is running
- Check `storage/logs/laravel.log`

## Next Steps

Now that TIKM is running:

1. **Explore Features**: Try creating tickets, managing users, and configuring settings
2. **Read User Guide**: See [User Guide](user-guide.md) for detailed usage instructions
3. **Configure Email**: Set up [Email Integration](../deployment/email-server.md) for production use
4. **Production Setup**: Follow [Production Deployment](../deployment/production.md) when ready

## Getting Help

- 📖 **Documentation**: Comprehensive guides in this documentation
- 🐛 **Issues**: Report bugs via GitHub Issues
- 💡 **Features**: Request features through GitHub Discussions
- 📧 **Support**: Contact support team for assistance

---

**Next**: [User Guide](user-guide.md) - Learn how to use TIKM effectively