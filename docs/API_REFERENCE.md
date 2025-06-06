# API Reference and Technical Documentation

## Architecture Overview

### Technology Stack
- **Framework**: Laravel 12.x (requires PHP 8.3+)
- **Admin Panel**: Filament 3.3 at `/admin`
- **Authentication**: Laravel Breeze
- **Database**: SQLite (file: `database/database.sqlite`)
- **Testing**: Pest PHP with Laravel plugin
- **Frontend Build**: Vite 6.2 with Tailwind CSS 4.0
- **Session/Cache/Queue**: Database drivers

### Database Design

The system is organized around "Offices" which function as departments:

- **Users** can be customers, agents, or admins
- **Agents** are assigned to one or more offices via many-to-many relationship
- **Tickets** belong to an office and can be assigned to agents within that office
- **Timeline logging** tracks all ticket activities automatically
- **Polymorphic attachments** can be added to tickets and replies
- **FAQs** can be general or department-specific with search capabilities

### Filament Admin Panel

The admin panel is configured in `app/Providers/Filament/AdminPanelProvider.php`:

- Auto-discovers resources, pages, and widgets in `app/Filament/`
- Dark mode enabled by default
- Primary color: Amber
- Role-based authentication middleware
- Restricted to admin and agent users only

## Key Implementation Details

### Email Notifications
- Uses Laravel's queued notifications for performance
- Intelligent recipient filtering to prevent notification spam
- Notifications sent for ticket creation and replies
- Email templates use Markdown with proper formatting

### Automatic Assignment
- `TicketAssignmentService` implements round-robin algorithm
- Cache-based tracking maintains fair distribution per office
- Supports manual reassignment and unassignment
- Timeline automatically logs all assignment changes

### File Management
- Public storage symlink created with `php artisan storage:link`
- File validation: 10MB max size, specific MIME types allowed
- Polymorphic attachments work with both tickets and replies
- Secure file handling with proper access controls

### FAQ System
- Searchable FAQ interface with department filtering
- Admin management through Filament with WYSIWYG editing
- Seeded with comprehensive sample content
- Integration prompts in ticket creation flow

### Authorization & Security
- Policy-based authorization for ticket access
- Customers can only view their own tickets
- Agents can only access tickets from their assigned offices
- Admins have full system access
- UUID-based public ticket references (no ID enumeration)

### Email-to-Ticket System
- **Artisan Command**: `php artisan ticket:process-email` processes raw emails from STDIN
- **Email Parsing**: Uses `php-mime-mail-parser` for robust MIME parsing
- **Address Handling**: Support emails use pattern `support+{uuid}@domain.com` for routing
- **Content Processing**: Intelligent HTML-to-text conversion and quoted reply filtering
- **User Management**: Automatically creates customer accounts for new email senders
- **Spam Protection**: Filters out bounce messages, auto-replies, and invalid senders
- **Integration**: Full integration with existing notification and assignment systems
- **Server Setup**: Comprehensive Dovecot/Postfix configuration guide in `EMAIL_SERVER_SETUP.md`

## Models and Relationships

### User Model
```php
// Roles
isAdmin() -> boolean
isAgent() -> boolean  
isCustomer() -> boolean

// Relationships
createdTickets() -> hasMany(Ticket)
assignedTickets() -> hasMany(Ticket)
offices() -> belongsToMany(Office)
ticketReplies() -> hasMany(TicketReply)
```

### Ticket Model
```php
// Attributes
uuid -> string (route key)
subject -> string
content -> text
creator_id -> foreignKey(User)
assigned_to_id -> foreignKey(User, nullable)
office_id -> foreignKey(Office)
ticket_status_id -> foreignKey(TicketStatus)
ticket_priority_id -> foreignKey(TicketPriority)

// Relationships
creator() -> belongsTo(User)
assignedTo() -> belongsTo(User)
office() -> belongsTo(Office)
status() -> belongsTo(TicketStatus)
priority() -> belongsTo(TicketPriority)
replies() -> hasMany(TicketReply)
attachments() -> morphMany(Attachment)
timeline() -> hasMany(TicketTimeline)
```

### Office Model
```php
// Attributes
name -> string
description -> text (nullable)

// Relationships
users() -> belongsToMany(User)
tickets() -> hasMany(Ticket)
faqs() -> hasMany(FAQ)
```

### TicketReply Model
```php
// Attributes
ticket_id -> foreignKey(Ticket)
user_id -> foreignKey(User)
message -> text

// Relationships
ticket() -> belongsTo(Ticket)
user() -> belongsTo(User)
attachments() -> morphMany(Attachment)
```

### Attachment Model (Polymorphic)
```php
// Attributes
attachable_type -> string
attachable_id -> integer
filename -> string
path -> string
size -> integer
mime_type -> string

// Relationships
attachable() -> morphTo()
```

## Services

### TicketAssignmentService
```php
autoAssignTicket(Ticket $ticket) -> void
reassignTicket(Ticket $ticket, User $newAgent) -> void
unassignTicket(Ticket $ticket) -> void
getAgentWorkload(User $agent) -> array
```

### EmailTicketService
```php
createTicketFromEmail(string $fromEmail, string $fromName, string $subject, string $content, array $attachments = []) -> ?Ticket
createReplyFromEmail(string $ticketUuid, string $fromEmail, string $fromName, string $subject, string $content, array $attachments = []) -> ?TicketReply
```

## Artisan Commands

### Email Processing
```bash
php artisan ticket:process-email
# Processes incoming email from STDIN
# Called by mail server via Sieve/Dovecot
# Handles both new tickets and replies
```

## Filament Resources

### TicketResource
- Complex filtering (status, priority, office, assignee)
- Bulk actions for status changes and assignments
- Rich view page with timeline display and action buttons
- Automatic timeline logging for all activities

### OfficeResource
- Department management with agent assignments
- Many-to-many relationship handling
- Nested resource management

### UserResource
- User management with role selection
- Office assignment interface
- Password reset functionality

### TicketStatusResource & TicketPriorityResource
- Settings group for system configuration
- Color-coded status and priority management
- Sort order and default value settings

### FAQResource
- Managing frequently asked questions
- Department-specific or general FAQs
- Publication status and ordering

## Notifications

### TicketCreated
- Sent to ticket creator and relevant staff
- Uses TicketCreatedMail mailable
- Includes Reply-To header with UUID

### TicketReplyAdded
- Sent to stakeholders when replies are added
- Uses TicketReplyMail mailable
- Intelligent recipient filtering

## Mail Classes

### TicketCreatedMail
- Supports Reply-To with UUID for email threading
- Conditional content based on recipient role
- Markdown template with branding

### TicketReplyMail
- Maintains conversation threading
- Shows reply context and history
- Links to full conversation view

## Policies

### TicketPolicy
```php
viewAny(User $user) -> bool
view(User $user, Ticket $ticket) -> bool
create(User $user) -> bool
update(User $user, Ticket $ticket) -> bool
delete(User $user, Ticket $ticket) -> bool
```

## Configuration

### Mail Configuration
```php
// config/mail.php
'support_domain' => env('MAIL_SUPPORT_DOMAIN', 'localhost')
```

### Environment Variables
```bash
MAIL_SUPPORT_DOMAIN=yourdomain.com
MAIL_FROM_ADDRESS=support@yourdomain.com
MAIL_FROM_NAME="Your App Support"
```

## Testing Approach

- Uses Pest PHP framework
- In-memory SQLite database for tests
- Test structure:
  - Feature tests: Test full application features
  - Unit tests: Test individual classes/methods
- Base test case configured in `tests/TestCase.php`

## Queue Configuration

Email notifications and processing use Laravel's queue system:

```bash
# Queue jobs (recommended for production)
php artisan queue:listen

# Process specific queue
php artisan queue:work --queue=emails,default
```

## Logging

- Email processing logs: `/var/log/ticket-email-processing.log`
- Laravel logs: `storage/logs/laravel.log`
- Comprehensive error tracking and debugging information

## Security Considerations

- CSRF protection on all forms
- XSS protection through Blade escaping
- File upload validation and sanitization
- Rate limiting on email processing
- SQL injection prevention through Eloquent ORM
- Authorization policies on all ticket operations