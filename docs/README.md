# TIKM Documentation

**TIKM** (Ticket Management System) is a comprehensive customer support platform built with Laravel 12 and Filament 3.3. This documentation serves as your complete guide to understanding, installing, configuring, and using TIKM effectively.

## 📚 Documentation Structure

This documentation is organized as a comprehensive guide, taking you from initial setup through advanced usage and customization. Each section builds upon the previous ones, but you can also jump directly to specific topics using the navigation below.

---

## 🚀 Quick Start

**New to TIKM?** Start here for a rapid overview:

- ⏱️ **5-minute setup**: Follow our [Quick Installation Guide](#chapter-2-installation--setup)
- 👤 **First login**: Use the [test credentials](#test-accounts) to explore
- 🎯 **Create your first ticket**: Follow the [Customer Guide](#chapter-3-user-guides)
- 🛠️ **Admin tour**: Explore the [Administrator Guide](#administrator-guide)

---

## 📖 Table of Contents

### **Part I: Introduction & Overview**

#### Chapter 1: System Overview
- [What is TIKM?](#what-is-tikm)
- [Key Features](#key-features)
- [Architecture Overview](#architecture-overview)
- [System Requirements](#system-requirements)
- [Test Accounts](#test-accounts)

---

### **Part II: Installation & Setup**

#### Chapter 2: Installation & Setup
- [Quick Development Setup](#development-setup)
- [📋 DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) - Production deployment
- [📧 EMAIL_SERVER_SETUP.md](EMAIL_SERVER_SETUP.md) - Mail server configuration
- [🔗 GITHUB_WEBHOOK_SETUP.md](GITHUB_WEBHOOK_SETUP.md) - Automated deployment setup

---

### **Part III: User Guides**

#### Chapter 3: User Guides
- [📖 USER_GUIDE.md](USER_GUIDE.md) - Complete user documentation
  - **Customer Guide** - Creating and managing tickets
  - **Agent Guide** - Handling customer support
  - **Administrator Guide** - System administration
  - **FAQ System** - Knowledge base management
  - **Email Integration** - Email-to-ticket workflows

---

### **Part IV: Advanced Configuration**

#### Chapter 4: Email-to-Ticket System
- [Email Server Integration](#email-integration)
- [Automated Ticket Creation](#automated-ticket-creation)
- [Email Threading & Routing](#email-routing)
- [Troubleshooting Email Issues](#email-troubleshooting)

#### Chapter 5: Customization & Configuration
- [Office Management](#office-management) - Internal vs External departments
- [Custom Fields & Forms](#custom-fields)
- [Notification Settings](#notifications)
- [Role-Based Permissions](#permissions)

---

### **Part V: Development & API**

#### Chapter 6: Technical Reference
- [🔧 API_REFERENCE.md](API_REFERENCE.md) - Complete technical documentation
- [⚡ ADVANCED_FEATURES.md](ADVANCED_FEATURES.md) - Advanced features guide and usage
- [📋 TICKETING_SYSTEM.md](TICKETING_SYSTEM.md) - Original system requirements
- [Database Schema](#database-schema)
- [Service Architecture](#service-architecture)

#### Chapter 7: Development Guide
- [Contributing Guidelines](#contributing)
- [Testing Framework](#testing)
- [Extension Development](#extensions)
- [API Usage Examples](#api-examples)

---

### **Part VI: Troubleshooting & Support**

#### Chapter 8: Troubleshooting
- [Common Issues](#common-issues)
- [Error Resolution](#error-resolution)
- [Performance Optimization](#performance)
- [Security Best Practices](#security)

#### Chapter 9: Support & Community
- [Getting Help](#getting-help)
- [Reporting Issues](#reporting-issues)
- [Contributing](#contributing)
- [Changelog](#changelog)

---

## 🎯 What is TIKM?

TIKM is a modern customer support ticketing system designed to streamline communication between customers and support teams. Built with Laravel's robust framework and Filament's powerful admin panel, TIKM provides:

### **🌟 Key Features**

#### **For Customers**
- 📝 **Easy Ticket Creation** - Submit support requests through web interface or email
- 📱 **Mobile-Friendly Interface** - Access tickets from any device
- 🔍 **FAQ Integration** - Self-service knowledge base with smart search
- 📧 **Email Notifications** - Stay updated on ticket progress
- 📎 **File Attachments** - Include screenshots, logs, and documents

#### **For Agents**
- 🎯 **Smart Assignment** - Automatic round-robin ticket distribution
- 📊 **Dashboard Analytics** - Track performance and workload
- 🏢 **Office-Based Organization** - Department-specific ticket management
- 💬 **Internal Notes** - Private team communication
- 🔄 **Bulk Operations** - Efficient mass ticket management

#### **For Administrators**
- 👥 **User Management** - Role-based access control (Customer/Agent/Admin)
- 🏢 **Office Configuration** - Internal vs external department setup
- ⚙️ **System Settings** - Customizable statuses, priorities, and workflows
- 📈 **Reporting** - Comprehensive analytics and insights
- 🔐 **Security Controls** - Advanced permissions and audit trails

#### **Technical Excellence**
- 📧 **Email-to-Ticket** - Full email integration with threading
- 🔄 **Real-time Updates** - Live notifications and status changes
- 🔌 **API-First Design** - RESTful API for integrations
- 🛡️ **Security-Focused** - CSRF protection, XSS prevention, secure file handling
- 📱 **Responsive Design** - Works seamlessly across all devices

---

## 🏗️ Architecture Overview

### **Technology Stack**

| Component | Technology | Purpose |
|-----------|------------|---------|
| **Backend** | Laravel 12.x | Core application framework |
| **Admin Panel** | Filament 3.3 | Rich administrative interface |
| **Authentication** | Laravel Breeze | User authentication system |
| **Database** | SQLite/MySQL/PostgreSQL | Data persistence |
| **Frontend** | Vite + Tailwind CSS | Modern build tools and styling |
| **Testing** | Pest PHP | Comprehensive testing framework |
| **Email** | Laravel Mail + Queues | Notification system |

### **Core Concepts**

#### **📁 Offices (Departments)**
The system is organized around **Offices**, which function as departments or support teams:

- **External Offices** (`is_internal = false`): Customer-facing departments visible in ticket creation (e.g., "Technical Support", "Sales")
- **Internal Offices** (`is_internal = true`): Staff-only departments for escalation and internal workflows (e.g., "Development Team")

#### **👥 User Roles**
- **Customers**: Submit and track their tickets through the frontend
- **Agents**: Handle tickets from their assigned offices via admin panel
- **Administrators**: Full system control and configuration access

#### **🎫 Ticket Lifecycle**
1. **Creation** - Customer submits ticket via web or email
2. **Assignment** - Automatic or manual assignment to agents
3. **Communication** - Threaded conversation with file attachments
4. **Resolution** - Status updates and final closure
5. **Analytics** - Performance tracking and reporting

---

## 💻 System Requirements

### **Development Environment**
- **PHP** 8.3 or higher
- **Composer** for dependency management
- **Node.js** 18+ and npm for frontend assets
- **Database** SQLite (default) or MySQL/PostgreSQL
- **Web Server** Apache or Nginx (optional for development)

### **Production Environment**
- **Memory** 512MB RAM minimum (1GB+ recommended)
- **Storage** 1GB available disk space
- **Email** SMTP server or email service integration
- **SSL** Certificate for secure connections
- **Queue Worker** For background job processing

---

## 🔑 Test Accounts

TIKM comes with pre-configured test accounts for immediate exploration:

| Role | Email | Password | Access Level |
|------|-------|----------|--------------|
| **Admin** | admin@example.com | password | Full system access |
| **Agent** | agent@example.com | password | Ticket management |
| **Customer** | customer@example.com | password | Ticket creation/viewing |

### **Quick Access URLs**
- **Customer Portal**: http://127.0.0.1:8000
- **Admin Panel**: http://127.0.0.1:8000/admin
- **Login Page**: http://127.0.0.1:8000/login
- **FAQ Section**: http://127.0.0.1:8000/faq

---

## ⚡ Development Setup

Get TIKM running locally in under 5 minutes:

```bash
# Clone and setup
git clone <repository-url> tikm
cd tikm

# Install dependencies
composer install
npm install

# Environment configuration
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Build frontend assets
npm run dev

# Start development server
composer dev  # Runs PHP server, queue listener, and Vite concurrently
```

**🎉 That's it!** Visit http://127.0.0.1:8000 to start using TIKM.

---

## 📧 Email Integration

TIKM's email-to-ticket system allows customers to create and respond to tickets via email:

### **Email Patterns**
- **New Tickets**: `support@yourdomain.com`
- **Replies**: `support+{ticket-uuid}@yourdomain.com`
- **Threading**: Automatic conversation threading

### **Features**
- Automatic user account creation
- Attachment preservation
- Intelligent quoted reply filtering
- Spam protection and bounce handling

For complete email server setup, see [EMAIL_SERVER_SETUP.md](EMAIL_SERVER_SETUP.md).

---

## 🏢 Office Management

Offices are the core organizational unit in TIKM, representing departments or support teams:

### **Office Types**

#### **External Offices** (Customer-Facing)
```php
is_internal = false  // Default setting
```
- Visible to customers in ticket creation forms
- Examples: "Technical Support", "Sales", "Customer Service"
- Used for public-facing support workflows

#### **Internal Offices** (Staff-Only)
```php
is_internal = true
```
- Hidden from customer interfaces
- Examples: "Development Team", "Management", "Quality Assurance"
- Used for internal escalation and specialized workflows

### **Agent Assignment**
- Agents can be assigned to multiple offices
- Round-robin automatic assignment within offices
- Manual reassignment and workload balancing

---

## 🛠️ Development Commands

### **Environment Management**
```bash
composer dev              # Start all development services
php artisan serve         # PHP development server only
npm run dev              # Vite dev server with hot reload
php artisan queue:listen  # Process queued jobs
php artisan pail         # View logs in real-time
```

### **Database Operations**
```bash
php artisan migrate              # Run migrations
php artisan migrate:fresh --seed # Fresh database with test data
php artisan db:seed             # Run seeders only
php artisan tinker              # Interactive REPL
```

### **Testing & Quality**
```bash
composer test                    # Run all tests
./vendor/bin/pint               # Fix PHP code style
php artisan test --filter=Ticket # Run specific tests
```

---

## 🔧 Common Issues

### **Quick Fixes**

| Issue | Solution |
|-------|----------|
| **Login Failed** | Check email/password, ensure account is active |
| **Cannot Create Tickets** | Verify customer role and form validation |
| **Email Not Working** | Check spam folder, verify SMTP configuration |
| **Admin Panel Access Denied** | Ensure user has Agent or Admin role |
| **File Upload Failed** | Check file size limits and MIME type restrictions |

### **Performance Tips**
- Enable PHP OpCache in production
- Use Redis/Memcached for session storage
- Configure queue workers for email processing
- Optimize database indexes for large datasets

---

## 🤝 Getting Help

### **Documentation First**
- 📖 Check this documentation for comprehensive guides
- 🔍 Use the search function to find specific topics
- 📧 Review email-specific setup in [EMAIL_SERVER_SETUP.md](EMAIL_SERVER_SETUP.md)

### **Community Support**
- 🐛 Report bugs via GitHub Issues
- 💡 Request features through GitHub Discussions
- 📧 Contact: [Your Support Email]
- 🌐 Community Forum: [Your Forum URL]

### **Professional Support**
- 🏢 Enterprise support available
- 🎓 Training and consultation services
- 🔧 Custom development and integration

---

## 📄 License

TIKM is open-source software licensed under the [MIT License](../LICENSE).

---

## 🚀 Next Steps

Ready to dive deeper? Choose your path:

### **🆕 New Users**
1. **Start Here**: [USER_GUIDE.md](USER_GUIDE.md) - Complete user documentation
2. **Setup Production**: [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) - Production deployment guide
3. **Configure Email**: [EMAIL_SERVER_SETUP.md](EMAIL_SERVER_SETUP.md) - Email integration setup

### **🛠️ Developers**
1. **Technical Deep Dive**: [API_REFERENCE.md](API_REFERENCE.md) - Complete technical documentation
2. **Advanced Features**: [ADVANCED_FEATURES.md](ADVANCED_FEATURES.md) - Search, avatars, security, SLA, and more
3. **System Requirements**: [TICKETING_SYSTEM.md](TICKETING_SYSTEM.md) - Original specifications
4. **Development Setup**: Follow the [development commands](#development-commands) above

### **🔧 Administrators**
1. **Admin Guide**: [USER_GUIDE.md](USER_GUIDE.md#admin-guide) - System administration
2. **Email Setup**: [EMAIL_SERVER_SETUP.md](EMAIL_SERVER_SETUP.md) - Production email configuration
3. **Deployment**: [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) - Production deployment

---

*This documentation is continuously updated. For the latest version, visit our [GitHub repository](https://github.com/your-org/tikm).*

**Last Updated**: January 2025 | **Version**: 1.0.0