# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a comprehensive ticketing system built with Laravel 12 and Filament 3.3, featuring email-to-ticket integration, role-based access control, and a complete customer support workflow.

## Development Setup

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+ and npm
- SQLite (for development)

### Quick Start

```bash
# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Frontend assets
npm run dev

# Start development server
composer dev  # Runs PHP server, queue listener, log viewer, and Vite concurrently
```

## CI/CD and Code Quality

### GitHub Actions Workflows
```bash
# Code quality checks run automatically on push/PR
# - Laravel Pint code style checking
# - Pest PHP test suite
# - PHP 8.2 compatibility validation

# Manual code style fixes
# Workflow: "Fix Code Style" can be triggered manually
# Auto-commits style fixes when needed
```

### Code Style Management
```bash
./vendor/bin/pint         # Fix PHP code style locally
./vendor/bin/pint --test  # Check style without fixing (used in CI)
```

## Development Commands

### Environment Management
```bash
composer dev              # Start all development services (recommended)
php artisan serve         # Start PHP development server only
npm run dev              # Start Vite dev server with hot reload
php artisan queue:listen  # Process queued jobs
php artisan pail         # View logs in real-time
```

### Database Operations
```bash
php artisan migrate              # Run database migrations
php artisan migrate:fresh        # Drop all tables and re-run migrations
php artisan migrate:fresh --seed # Fresh migration with seeders
php artisan db:seed             # Run database seeders
php artisan tinker              # Interactive REPL
```

### Testing
```bash
composer test                           # Run all tests (clears config cache first)
php artisan test                       # Run tests directly
php artisan test --filter=ExampleTest  # Run specific test
php artisan test tests/Feature         # Run feature tests only
php artisan test tests/Unit            # Run unit tests only
```

### Code Quality
```bash
./vendor/bin/pint         # Fix PHP code style (Laravel's opinionated formatter)
./vendor/bin/pint --test  # Check style without fixing
```

### Email-to-Ticket Development
```bash
php artisan ticket:process-email  # Process incoming email from STDIN

# Testing email processing manually:
echo -e "From: test@example.com\nTo: support@domain.com\nSubject: Test\n\nTest message" | php artisan ticket:process-email

# Monitor email processing:
tail -f /var/log/ticket-email-processing.log
tail -f storage/logs/laravel.log
```

### Production Build
```bash
npm run build            # Build frontend assets
php artisan optimize     # Cache configs, routes, and views
```

## Architecture

### Technology Stack
- **Framework**: Laravel 12.x
- **Admin Panel**: Filament 3.3 at `/admin`
- **Authentication**: Laravel Breeze
- **Database**: SQLite (development) / MySQL/PostgreSQL (production)
- **Testing**: Pest PHP with Laravel plugin
- **Frontend**: Vite 6.2 with Tailwind CSS 4.0
- **Email Processing**: php-mime-mail-parser
- **Session/Cache/Queue**: Database drivers

### Key Directories
```
app/
├── Console/Commands/     # Artisan commands (email processing)
├── Filament/            # Admin panel resources
│   └── Resources/       # CRUD interfaces for models
├── Http/Controllers/    # Web controllers (customer frontend)
├── Mail/               # Mailable classes with Reply-To headers
├── Models/             # Eloquent models
├── Notifications/      # Email notifications
├── Policies/           # Authorization policies
└── Services/           # Business logic services

resources/
├── views/
│   ├── emails/         # Email templates
│   ├── faq/           # FAQ pages
│   └── tickets/       # Customer ticket interface

docs/                   # Documentation
├── API_REFERENCE.md    # Technical documentation
├── DEPLOYMENT_GUIDE.md # Production deployment
├── EMAIL_SERVER_SETUP.md # Mail server configuration
├── TICKETING_SYSTEM.md # Original requirements
└── USER_GUIDE.md       # End-user documentation
```

## Database Schema

### Core Models
- **User**: Customers, agents, and admins with role-based permissions
- **Office**: Departments/categories for ticket organization
- **Ticket**: Support tickets with UUID routing, status, and priority
- **TicketReply**: Conversation messages with polymorphic attachments
- **TicketTimeline**: Audit trail for all ticket activities
- **Attachment**: File uploads (polymorphic - tickets and replies)
- **FAQ**: Knowledge base articles with department filtering

### Key Relationships
- Users → Offices (many-to-many for agent assignments)
- Tickets → Office (belongs to)
- Tickets → User (creator and assigned agent)
- Tickets → TicketStatus/TicketPriority (configuration)
- TicketReply → Ticket/User (conversation participants)
- Attachments → Ticket/TicketReply (polymorphic)

## Feature Implementation

### Customer Frontend
- **Location**: `resources/views/tickets/`, `app/Http/Controllers/TicketController.php`
- **Features**: Ticket creation, viewing, replies, file uploads
- **Authentication**: Laravel Breeze integration
- **Authorization**: Policy-based (customers see only their tickets)

### Admin Panel
- **Location**: `app/Filament/Resources/`
- **Resources**: Ticket, User, Office, TicketStatus, TicketPriority, FAQ
- **Features**: Advanced filtering, bulk actions, timeline view, assignment management
- **Access**: Restricted to agents and admins via middleware

### Email Integration
- **Command**: `app/Console/Commands/ProcessIncomingEmail.php`
- **Service**: `app/Services/EmailTicketService.php`
- **Flow**: STDIN → Parse → Create/Reply → Notify
- **Addresses**: `support@domain.com` (new) / `support+{uuid}@domain.com` (replies)

### Notifications
- **Classes**: `TicketCreated`, `TicketReplyAdded`
- **Mailables**: `TicketCreatedMail`, `TicketReplyMail`
- **Features**: Queued delivery, Reply-To headers, role-specific content

### Assignment System
- **Service**: `app/Services/TicketAssignmentService.php`
- **Algorithm**: Round-robin per office with cache-based tracking
- **Features**: Auto-assignment, manual reassignment, workload tracking

## Development Guidelines

### Adding New Features

1. **Models**: Create with proper relationships and fillable attributes
2. **Migrations**: Include indexes for performance-critical queries
3. **Policies**: Implement authorization for all user-accessible models
4. **Resources**: Add Filament resources for admin functionality
5. **Tests**: Write feature and unit tests for new functionality

### Code Style
- Follow Laravel coding standards
- Use `./vendor/bin/pint` for automatic formatting
- Implement type hints and return types
- Document complex business logic

### Security Considerations
- Use authorization policies for all data access
- Validate file uploads (type, size, content)
- Sanitize user input in email processing
- Use UUID routing to prevent enumeration attacks

### Performance Optimization
- Use database indexes for common queries
- Implement caching for frequently accessed data
- Queue email notifications for better response times
- Optimize N+1 queries with eager loading

## Testing Strategy

### Test Structure
- **Feature Tests**: Full application workflows (ticket creation, email processing)
- **Unit Tests**: Individual service methods and business logic
- **Database**: In-memory SQLite for fast test execution

### Key Test Areas
- Authentication and authorization
- Ticket CRUD operations
- Email-to-ticket processing
- Notification delivery
- File upload handling

### Running Tests
```bash
composer test                    # Full test suite
php artisan test --filter=Ticket # Ticket-related tests only
php artisan test tests/Feature   # Integration tests
php artisan test tests/Unit      # Unit tests
```

## Common Development Tasks

### Adding a New Ticket Status
1. Create migration: `php artisan make:migration add_new_status_to_ticket_statuses`
2. Update seeder: `database/seeders/TicketStatusSeeder.php`
3. Test with existing workflows

### Creating a New User Role
1. Update User model with role check method
2. Modify authorization policies
3. Update Filament middleware if needed
4. Add role-specific dashboard content

### Adding Email Templates
1. Create Mailable class: `php artisan make:mail TemplateName`
2. Design Markdown template in `resources/views/emails/`
3. Configure Reply-To headers for threading
4. Test email rendering: `php artisan tinker`

### Modifying Assignment Logic
1. Update `TicketAssignmentService`
2. Modify auto-assignment triggers
3. Test round-robin distribution
4. Update timeline logging

## Debugging

### Common Issues
- **Permission errors**: Check file permissions on `storage/` directories
- **Email not processing**: Verify queue worker is running
- **Filament access denied**: Check user role and middleware configuration
- **Attachment upload fails**: Check file size limits and MIME type validation

### Debug Tools
- `php artisan tinker` - Interactive REPL
- `php artisan pail` - Real-time log viewing
- Laravel Debugbar (development)
- `tail -f storage/logs/laravel.log` - Log monitoring

## Documentation

For complete documentation, see the `docs/` directory:
- **USER_GUIDE.md**: End-user instructions
- **API_REFERENCE.md**: Technical architecture details
- **DEPLOYMENT_GUIDE.md**: Production deployment instructions
- **EMAIL_SERVER_SETUP.md**: Mail server configuration

## Contributing

When extending this system:
1. Follow Laravel best practices
2. Maintain backward compatibility
3. Update relevant documentation
4. Add comprehensive tests
5. Consider security implications
6. Update this CLAUDE.md file for significant architectural changes

This ticketing system provides a solid foundation for customer support operations with room for customization and expansion based on specific business needs.