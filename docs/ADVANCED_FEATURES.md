# TIKM Advanced Features Guide

This document covers the advanced features implemented in TIKM beyond the core ticketing functionality. These features enhance productivity, security, and user experience for both agents and customers.

## 🔍 Full-Text Search System

### Overview
Advanced search functionality across tickets and FAQs with Laravel Scout integration.

### Features
- **Global Search**: Search across tickets and FAQs from the navigation bar
- **Content Indexing**: Automatic indexing of ticket subjects, content, and FAQ titles/content
- **Result Filtering**: Filter results by type (tickets, FAQs, or all)
- **Pagination**: Efficient handling of large result sets
- **Real-time Updates**: Search index updates automatically when content changes

### Usage

#### Customer/Agent Search
1. Use the search bar in the top navigation
2. Enter keywords to search across all content
3. Results show tickets you have access to and public FAQs
4. Click results to navigate directly to tickets/FAQs

#### Advanced Search (Coming Soon)
- Date range filtering
- Status and priority filters
- Saved searches
- Export capabilities

### Technical Details
- **Engine**: Laravel Scout with database driver
- **Models**: Ticket and FAQ models are searchable
- **Index Fields**: subject, content, title (FAQs)
- **Performance**: Optimized with proper database indexing

---

## 👤 Dynamic Avatar System

### Overview
Role-based avatar system using UI Avatars service with automatic color coding and custom upload support.

### Features
- **Role-Based Colors**: 
  - Admins: Red (#dc2626)
  - Agents: Blue (#2563eb)
  - Customers: Green (#16a34a)
- **Automatic Initials**: Generated from user names
- **Custom Uploads**: Support for custom avatar images
- **Responsive Sizing**: Multiple size options (xs, sm, md, lg, xl, 2xl)
- **Filament Integration**: Avatars display in admin panel

### Usage

#### For Users
1. Avatar automatically displays based on your role
2. Upload custom avatar through profile settings (coming soon)
3. Initials are auto-generated from your name

#### For Developers
```php
// Display user avatar in views
<x-user-avatar :user="$user" size="md" />

// Available sizes: xs, sm, md, lg, xl, 2xl
// Additional CSS classes can be added with class="" attribute
```

### Implementation Notes
- **CSP Configuration**: Requires `ui-avatars.com` in Content Security Policy
- **Fallback System**: Graceful degradation to initials if custom avatar fails
- **Performance**: Lazy loading and caching for optimal performance

---

## 📝 Internal Notes System

### Overview
Private communication system for agents and admins that customers cannot see.

### Features
- **Role-Based Visibility**: Only agents and admins can see internal notes
- **Visual Indicators**: Yellow border and "Internal Note" badge
- **No Customer Notifications**: Internal notes don't trigger customer emails
- **Filament Integration**: Add internal notes directly from admin panel
- **Timeline Integration**: Internal notes appear in ticket timeline for agents

### Usage

#### Creating Internal Notes (Customer Frontend)
1. Navigate to any ticket you have access to
2. Scroll to the reply form
3. Check the "Internal Note" checkbox (only visible to agents/admins)
4. Type your private message
5. Submit - customers will not see this reply

#### Creating Internal Notes (Admin Panel)
1. Open any ticket in Filament admin
2. Click "Add Internal Note" button in header actions
3. Enter your internal message
4. Submit - note is added to timeline

#### Viewing Internal Notes
- **Customers**: Cannot see internal notes (filtered out automatically)
- **Agents/Admins**: See all replies including internal notes with special styling
- **Visual Indicators**: Internal notes have yellow left border and badge

### Best Practices
- Use internal notes for:
  - Agent-to-agent communication
  - Technical details customers shouldn't see
  - Escalation notes and case history
  - Debugging information
- Keep customer-facing replies separate from internal discussions

---

## 📊 Analytics Dashboard

### Overview
Comprehensive analytics and reporting system providing actionable insights into support performance, agent productivity, and customer satisfaction metrics.

### Features
- **Real-time Metrics**: Live dashboard updates with key performance indicators
- **Date Range Filtering**: Flexible reporting periods (7, 30, 90, 365 days)
- **Multi-dimensional Analysis**: Tickets, agents, departments, and customer insights
- **Visual Reporting**: Interactive charts and progress indicators
- **SLA Monitoring**: Response and resolution compliance tracking
- **Performance Optimization**: Efficient queries with proper caching

### Key Metrics Categories

#### Overview Metrics
- **Total Tickets**: All-time ticket count
- **New Tickets**: Created within selected date range
- **Resolved Tickets**: Closed within selected date range
- **Open Tickets**: Currently active tickets
- **Resolution Rate**: Percentage of tickets resolved
- **Average Response Time**: Time to first agent response
- **Average Resolution Time**: Time to ticket closure

#### Agent Performance
- **Individual Metrics**: Per-agent performance tracking
- **Resolution Rates**: Percentage of assigned tickets resolved
- **Response Times**: Average time to customer responses
- **Workload Distribution**: Tickets assigned and completed
- **Reply Activity**: Number of customer-facing replies sent

#### Department Analysis
- **Office Performance**: Department-specific metrics
- **Ticket Distribution**: Volume by department
- **Resolution Efficiency**: Average resolution times per office
- **Workload Balance**: Ticket distribution across teams

#### SLA Compliance
- **Response SLA**: Percentage meeting response time targets
- **Resolution SLA**: Percentage meeting resolution time targets
- **Breach Tracking**: Count and identification of SLA violations
- **Compliance Trends**: Historical SLA performance

#### Customer Insights
- **Top Customers**: Customers with highest ticket volume
- **Ticket Patterns**: Customer behavior analysis
- **Support Frequency**: Customer engagement metrics

### Usage

#### Accessing Analytics (Agents & Admins Only)
1. Navigate to "Analytics" in the main navigation
2. Select desired date range from dropdown
3. Review overview metrics cards
4. Analyze detailed breakdowns in tables and charts
5. Export data (coming soon) for external reporting

#### Filament Admin Integration
- Analytics overview widget on admin dashboard
- Real-time metrics display
- Quick access to key performance indicators
- Color-coded status indicators for SLA compliance

#### Date Range Options
- **Last 7 days**: Short-term performance tracking
- **Last 30 days**: Monthly reporting (default)
- **Last 90 days**: Quarterly analysis
- **Last year**: Annual performance review

### Technical Implementation

#### Performance Optimization
```php
// Efficient agent metrics query
$agents = User::where('role', 'agent')
    ->withCount(['assignedTickets as total_assigned'])
    ->get()
    ->map(function ($agent) use ($startDate, $endDate) {
        // Calculate performance metrics
        return [
            'resolution_rate' => $this->calculateResolutionRate($agent),
            'avg_response_time' => $this->getResponseTime($agent),
            // ... other metrics
        ];
    });
```

#### Chart Integration
```javascript
// Daily trends visualization
new Chart(ctx, {
    type: 'line',
    data: {
        labels: trendData.map(item => item.date),
        datasets: [{
            label: 'Tickets',
            data: trendData.map(item => item.tickets),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
        }]
    }
});
```

#### Duration Formatting
Smart time formatting for readability:
- **Minutes**: "45m"
- **Hours**: "2h 30m"
- **Days**: "3d 5h"

### Security & Access Control
- **Role Restriction**: Only agents and admins can access analytics
- **Data Filtering**: Users see only relevant data based on permissions
- **Query Optimization**: Indexed database queries for performance
- **Input Validation**: Secure date range and parameter handling

### API Integration (Future)
Planned endpoints for external reporting:
```php
// Planned API endpoints
GET /api/analytics/overview
GET /api/analytics/agents/{id}/performance
GET /api/analytics/departments/{id}/metrics
GET /api/analytics/export/{format}
```

### Best Practices

#### For Managers
1. **Regular Review**: Check metrics weekly for trend identification
2. **Agent Support**: Use performance data to provide targeted training
3. **Resource Planning**: Analyze workload for staffing decisions
4. **SLA Monitoring**: Track compliance and adjust targets as needed

#### For Agents
1. **Self-Assessment**: Monitor personal performance metrics
2. **Goal Setting**: Use data to establish improvement targets
3. **Time Management**: Optimize response and resolution times
4. **Customer Focus**: Track customer satisfaction indicators

#### For Administrators
1. **System Monitoring**: Regular performance analysis
2. **Capacity Planning**: Use trends for infrastructure decisions
3. **Process Optimization**: Identify bottlenecks and inefficiencies
4. **Reporting**: Generate executive summaries from analytics data

---

## 📋 Canned Responses System

### Overview
Pre-written response templates for agents to improve efficiency and maintain consistent communication standards across all customer interactions.

### Features
- **Template Management**: Create, edit, and organize response templates
- **Variable Replacement**: Dynamic content insertion with ticket/customer data
- **Category Organization**: Group responses by type (General, Technical, Billing, etc.)
- **Usage Analytics**: Track template usage frequency and effectiveness
- **Role-Based Access**: Public templates for all agents or private personal templates
- **Search & Filter**: Quick template discovery with search and category filters
- **Integration**: Seamless integration into ticket reply interface

### Key Benefits
- **Faster Response Times**: Reduce typing time with pre-written templates
- **Consistent Messaging**: Ensure uniform communication standards
- **Quality Assurance**: Maintain professional tone and accuracy
- **Agent Productivity**: Focus on problem-solving rather than writing
- **Scalable Training**: New agents can learn from proven templates

### Template Variables
Available placeholders for dynamic content:
- `{{customer_name}}` - Customer's full name
- `{{customer_email}}` - Customer's email address
- `{{ticket_id}}` - Ticket UUID/reference number
- `{{ticket_subject}}` - Ticket subject line
- `{{agent_name}}` - Current agent's full name
- `{{company_name}}` - Company name from configuration
- `{{current_date}}` - Current date (formatted)
- `{{current_time}}` - Current time (formatted)

### Usage Instructions

#### Creating Templates (Admin Panel)
1. Navigate to **Canned Responses** in Filament admin
2. Click **Create** to add new template
3. Fill in template details:
   - **Title**: Brief descriptive name
   - **Category**: Organize by type (General, Technical, etc.)
   - **Content**: Template text with variables
   - **Public**: Allow all agents to use this template
   - **Active**: Enable/disable template availability
4. Save template

#### Using Templates (Ticket Interface)
1. Open any ticket as an agent/admin
2. Scroll to the reply form
3. Click **Show Templates** in the Canned Responses section
4. Search or filter templates by category
5. Click on a template to preview with live data
6. Click **Use Template** to insert into reply field
7. Customize content as needed before sending

### Administrative Features

#### Template Management
- **Bulk Operations**: Activate/deactivate multiple templates
- **Usage Statistics**: Track which templates are most effective
- **Access Control**: Manage public vs private template permissions
- **Category Management**: Create custom categories for organization

#### Analytics Integration
- **Usage Tracking**: Monitor template adoption rates
- **Response Time Improvement**: Measure efficiency gains
- **Quality Metrics**: Track customer satisfaction with templated responses
- **Performance Insights**: Identify most successful templates

### Template Categories

#### Default Categories
- **General**: Welcome messages, acknowledgments, follow-ups
- **Technical**: Troubleshooting steps, technical explanations
- **Billing**: Payment issues, refund processes, account queries
- **Account**: Password resets, profile updates, access issues
- **Shipping**: Delivery information, tracking updates
- **Returns**: Return policies, refund procedures
- **Escalation**: Handoff messages, complexity notifications
- **Closing**: Resolution confirmations, satisfaction surveys

### Best Practices

#### Template Creation
1. **Clear Subject Lines**: Use descriptive titles for easy identification
2. **Professional Tone**: Maintain consistent brand voice
3. **Variable Usage**: Leverage personalization for better customer experience
4. **Concise Content**: Keep templates focused and actionable
5. **Regular Updates**: Review and refresh templates based on feedback

#### Template Organization
1. **Logical Categories**: Group similar responses together
2. **Naming Conventions**: Use consistent naming patterns
3. **Public vs Private**: Share effective templates with the team
4. **Version Control**: Archive outdated templates rather than deleting

#### Usage Guidelines
1. **Personalization**: Always customize templates for specific situations
2. **Context Awareness**: Ensure template matches customer's issue
3. **Follow-up Actions**: Include next steps when appropriate
4. **Quality Review**: Proofread before sending templated responses

### API Integration
```php
// Retrieve available templates
GET /api/canned-responses

// Get specific template with variables
GET /api/canned-responses/{id}

// Preview template with ticket data
POST /api/canned-responses/{id}/preview

// Use template and track usage
POST /api/canned-responses/{id}/use
```

### Security Features
- **Authorization Policies**: Role-based access control
- **Input Validation**: Secure template content processing
- **XSS Protection**: Safe variable replacement
- **Audit Logging**: Track template usage and modifications

### Performance Optimization
- **Efficient Queries**: Optimized database access with proper indexing
- **Caching Strategy**: Template data caching for faster loading
- **Lazy Loading**: On-demand template interface loading
- **Usage Analytics**: Background tracking without performance impact

### Future Enhancements
- **AI-Powered Suggestions**: Recommend templates based on ticket content
- **Multi-language Support**: Localized template variations
- **Advanced Variables**: Complex data structures and calculations
- **Template Sharing**: Import/export templates between systems
- **Approval Workflows**: Review process for public templates

---

## 🛡️ Security Hardening

### Overview
Multi-layer security implementation protecting against common web vulnerabilities.

### Features Implemented

#### Rate Limiting
- **Endpoint Protection**: Login, registration, ticket creation, and search
- **Configurable Limits**: Different limits per endpoint type
- **IP-Based Tracking**: Prevents abuse from single sources
- **Graceful Degradation**: Clear error messages when limits exceeded

#### Content Security Policy (CSP)
- **Script Sources**: Controlled JavaScript execution
- **Style Sources**: Restricted CSS loading for security
- **Image Sources**: Whitelisted domains including avatar services
- **Font Sources**: Secure font loading from trusted CDNs
- **Development Support**: Relaxed policies for local development

#### Additional Security Measures
- **Input Validation**: Comprehensive request validation
- **CSRF Protection**: Automatic token verification
- **XSS Prevention**: Output escaping and sanitization
- **File Upload Security**: Type and size restrictions

### Configuration
```php
// Rate limiting configuration in middleware
'tickets' => 60,      // 60 requests per minute for ticket operations
'auth' => 5,          // 5 login attempts per minute
'search' => 120,      // 120 search requests per minute
```

### CSP Domains
Current whitelist includes:
- `ui-avatars.com` (avatar service)
- `fonts.bunny.net` (font service)
- `cdn.jsdelivr.net` (CDN assets)
- `fonts.googleapis.com` (Google Fonts)

---

## ⏱️ SLA Management System

### Overview
Comprehensive Service Level Agreement tracking with business hours calculation and automated monitoring.

### Features
- **Response SLA**: Track time to first agent response
- **Resolution SLA**: Monitor ticket resolution timeframes  
- **Business Hours**: Configurable business days and hours
- **Holiday Support**: Exclude holidays from SLA calculations
- **Automatic Tracking**: SLA times calculated on ticket creation
- **Breach Detection**: Identify and flag SLA violations

### Business Hours Configuration
Default settings (configurable in SLA model):
- **Business Days**: Monday - Friday
- **Business Hours**: 9:00 AM - 5:00 PM
- **Timezone**: System timezone
- **Holidays**: Configurable exclusion list

### SLA Calculation
```php
// Example SLA times by priority
High Priority: 2 business hours response, 8 business hours resolution
Medium Priority: 4 business hours response, 24 business hours resolution  
Low Priority: 8 business hours response, 72 business hours resolution
```

### Usage

#### For Agents
- SLA due times display on ticket views
- Color-coded indicators show SLA status
- Breached SLAs highlighted in red
- Dashboard shows SLA performance metrics

#### For Administrators
- Configure SLA rules in Filament admin
- Set response and resolution timeframes
- Define business hours and holidays
- Monitor SLA compliance across teams

### API Integration
```php
// Check SLA status programmatically
$ticket->isResponseSlaBreached();    // Returns boolean
$ticket->isResolutionSlaBreached();  // Returns boolean
$ticket->getResponseTimeRemaining(); // Returns formatted time string
$ticket->getResolutionTimeRemaining(); // Returns formatted time string
```

---

## 🧪 Browser Testing Infrastructure

### Overview
Automated browser testing using Symfony Panther for end-to-end UI testing.

### Features
- **Cross-Browser Testing**: Firefox and Chrome support
- **Screenshot Capture**: Automatic screenshots on test failures
- **JavaScript Testing**: Full browser environment with JS execution
- **User Interaction**: Simulate real user workflows
- **CI/CD Integration**: Automated testing in GitHub Actions

### Test Categories
- **UI Components**: Form submissions, navigation, search
- **User Workflows**: Ticket creation, replies, status changes
- **Role-Based Access**: Permission testing across user roles
- **Search Functionality**: End-to-end search testing

### Running Browser Tests
```bash
# Run all browser tests
php artisan test tests/Browser/

# Run specific browser test
php artisan test tests/Browser/SearchTest.php

# Run with specific browser
php artisan test tests/Browser/ --env=panther-firefox
```

### Screenshot Location
Failed test screenshots are saved to:
- `tests/Browser/screenshots/`
- Automatic cleanup after successful runs
- Timestamped for easy identification

---

## 📊 Performance Optimizations

### Overview
Database and query optimizations implemented throughout the system.

### N+1 Query Elimination
All major queries optimized with eager loading:
```php
// Ticket listings with relationships
$tickets = Ticket::with(['creator', 'assignedTo', 'status', 'priority', 'office'])->get();

// Search results with efficient loading
$results = Ticket::search($query)->with(['creator', 'office', 'status'])->paginate();
```

### Database Indexing
Strategic indexes on frequently queried fields:
- `tickets.uuid` (unique, for routing)
- `ticket_replies.is_internal` (for filtering)
- `ticket_replies.ticket_id, created_at` (composite, for timeline)
- `users.role` (for role-based queries)

### Caching Strategy
- **Avatar URLs**: Cached avatar generation
- **Search Results**: Query result caching for common searches
- **SLA Calculations**: Cached business hour calculations

---

## 🔧 Development Tools

### Overview
Tools and configurations to enhance the development experience.

### Code Quality
- **Laravel Pint**: Automated code formatting
- **Pest PHP**: Modern testing framework
- **GitHub Actions**: Automated CI/CD pipeline
- **Type Hints**: Comprehensive type annotations

### Database Tools
```bash
# Reset and seed development data
php artisan migrate:fresh --seed

# Generate test data
php artisan tinker # Then use factories

# Check database structure
php artisan tinker --execute="echo json_encode(Schema::getColumnListing('table_name'));"
```

### Testing Commands
```bash
# Run specific feature tests
composer test                        # Full test suite
php artisan test tests/Feature/      # Feature tests only
php artisan test tests/Unit/         # Unit tests only
php artisan test --filter=Internal   # Tests matching pattern
```

### Development Server
```bash
# Start all development services
composer dev

# Individual services
php artisan serve        # Web server
npm run dev             # Vite assets
php artisan queue:listen # Background jobs
php artisan pail        # Log viewer
```

---

## 🎯 Best Practices

### Security
1. **Always validate input** at controller level
2. **Use authorization policies** for data access
3. **Sanitize file uploads** with type/size restrictions
4. **Keep CSP updated** when adding external services
5. **Monitor rate limits** in production logs

### Performance
1. **Use eager loading** for related data
2. **Implement database indexes** for common queries
3. **Cache expensive operations** (SLA calculations, search)
4. **Optimize images and assets** for faster loading
5. **Monitor query performance** with debugging tools

### User Experience
1. **Provide clear feedback** for user actions
2. **Use loading states** for slow operations
3. **Implement progressive enhancement** for JavaScript features
4. **Ensure accessibility** with proper ARIA labels
5. **Test across different devices** and browsers

### Development
1. **Write tests first** for new features
2. **Use type hints** and return types
3. **Document complex logic** with clear comments
4. **Follow Laravel conventions** for consistency
5. **Keep migrations reversible** for safe deployments

---

## 📚 Additional Resources

### Documentation
- [User Guide](USER_GUIDE.md) - End-user instructions
- [API Reference](API_REFERENCE.md) - Technical API documentation
- [Deployment Guide](DEPLOYMENT_GUIDE.md) - Production setup
- [Email Setup](EMAIL_SERVER_SETUP.md) - Mail server configuration

### Configuration Files
- `CLAUDE.md` - AI assistant instructions and commands
- `phpunit.xml` - Test configuration
- `tailwind.config.js` - UI styling configuration
- `.env.example` - Environment variables template

### Support and Contributing
- **Issues**: Report bugs and request features
- **Testing**: Run comprehensive test suite before changes
- **Documentation**: Update relevant docs when adding features
- **Security**: Follow responsible disclosure for security issues

This advanced features guide will be updated as new functionality is added to the TIKM system.