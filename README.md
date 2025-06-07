# TIKM

[![Code Quality](https://github.com/markc/tickets/workflows/Code%20Quality/badge.svg)](https://github.com/markc/tickets/actions)

A comprehensive customer support system built with Laravel 12 and Filament 3.3, featuring email-to-ticket integration, role-based access control, and a complete support workflow.

## Features

### ✅ Core Functionality
- **Complete ticketing workflow** with status tracking and priority management
- **Role-based access control** (Customers, Agents, Admins)
- **Email-to-ticket integration** with real-time processing
- **File attachment support** with secure validation
- **FAQ system** with search and department filtering
- **Automatic ticket assignment** using round-robin algorithm
- **Real-time notifications** via email with threading support
- **Comprehensive audit trails** for all ticket activities

### 🎯 User Experience
- **Customer portal** for ticket creation and tracking
- **Admin panel** with advanced filtering and bulk actions
- **Email threading** for seamless communication
- **Mobile-responsive design** with Tailwind CSS
- **Dashboard widgets** with statistics and recent activity
- **Timeline view** showing complete ticket history

### 🔧 Technical Features
- **Laravel 12** with modern PHP 8.2+ features
- **Filament 3.3** admin panel with dark mode
- **SQLite** for development, MySQL/PostgreSQL for production
- **Pest PHP** testing framework with comprehensive coverage
- **Vite** build system with hot reload
- **Queue-based** email processing for performance
- **UUID-based** public routing for security

## Quick Start

### Prerequisites
- PHP 8.3+
- Composer
- Node.js 18+ and npm

### Installation

```bash
# Clone and setup
git clone <repository-url> tickets
cd tickets

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database and assets
php artisan migrate --seed
npm run dev

# Start development server
composer dev
```

Visit http://127.0.0.1:8000 to access the application.

### Test Accounts
- **Admin**: admin@example.com / password
- **Agent**: agent@example.com / password  
- **Customer**: customer@example.com / password

## Documentation

### 📖 User Guides
- **[User Guide](docs/USER_GUIDE.md)** - Complete guide for customers, agents, and administrators
- **[TIKM System Overview](docs/TICKETING_SYSTEM.md)** - Original system requirements and specifications

### 🛠️ Development
- **[CLAUDE.md](CLAUDE.md)** - Development setup and architecture guide
- **[API Reference](docs/API_REFERENCE.md)** - Technical documentation and code reference

### 🚀 Deployment
- **[Deployment Guide](docs/DEPLOYMENT_GUIDE.md)** - Production deployment instructions
- **[GitHub Webhook Setup](docs/GITHUB_WEBHOOK_SETUP.md)** - Automated deployment configuration
- **[Email Server Setup](docs/EMAIL_SERVER_SETUP.md)** - Mail server configuration for email-to-ticket

## Development

### Starting Development Environment
```bash
composer dev  # Starts PHP server, queue worker, log viewer, and Vite
```

### Running Tests
```bash
composer test              # Full test suite
php artisan test          # Laravel test runner
./vendor/bin/pint         # Code formatting
```

### Key Commands
```bash
php artisan migrate --seed           # Reset database with sample data
php artisan ticket:process-email     # Process email (called by mail server)
php artisan queue:listen            # Process background jobs
```

## Architecture

### Technology Stack
- **Backend**: Laravel 12, PHP 8.2+
- **Admin Panel**: Filament 3.3
- **Frontend**: Blade templates, Tailwind CSS 4.0, Alpine.js
- **Database**: SQLite (dev), MySQL/PostgreSQL (prod)
- **Email**: php-mime-mail-parser for email processing
- **Testing**: Pest PHP with Laravel integration

### Key Components
- **Customer Portal**: Ticket creation, viewing, and replies
- **Admin Panel**: Complete ticket management with filtering and bulk actions
- **Email Integration**: Bidirectional email-to-ticket processing
- **FAQ System**: Knowledge base with search and categorization
- **Notification System**: Email notifications with proper threading
- **Assignment System**: Automatic round-robin ticket distribution

## Email-to-Ticket Features

### How It Works
1. **Email arrives** at `support@yourdomain.com`
2. **Mail server** pipes email to Laravel command
3. **System processes** email and creates ticket/reply
4. **Notifications sent** with Reply-To headers for threading
5. **Customers reply** to continue conversation

### Email Address Patterns
- **New tickets**: `support@yourdomain.com`
- **Replies**: `support+{ticket-uuid}@yourdomain.com`
- **Automatic routing** based on UUID in email address

## Contributing

1. Follow Laravel coding standards
2. Write tests for new features
3. Update documentation as needed
4. Use `./vendor/bin/pint` for code formatting
5. Submit pull requests with clear descriptions

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## Support

For questions about using this system:
- Check the [User Guide](docs/USER_GUIDE.md)
- Review [API Reference](docs/API_REFERENCE.md) for technical details
- Create an issue for bugs or feature requests

For development questions:
- Refer to [CLAUDE.md](CLAUDE.md) for setup and architecture
- Check [Deployment Guide](docs/DEPLOYMENT_GUIDE.md) for production setup
- Review the codebase for examples and patterns

---

**Built with ❤️ using Laravel and Filament**