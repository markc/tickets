# TIKM User Guide

This guide covers how to use TIKM from both customer and administrative perspectives.

## Access Points

- **Customer Frontend**: http://127.0.0.1:8000
- **Admin Panel**: http://127.0.0.1:8000/admin
- **Login**: http://127.0.0.1:8000/login
- **FAQ Page**: http://127.0.0.1:8000/faq

## Test Credentials

- **Admin**: admin@example.com / password
- **Agent**: agent@example.com / password
- **Customer**: customer@example.com / password

## Customer Guide

### Creating Your First Ticket

1. **Register/Login**: Create an account or login at `/login`
2. **Dashboard**: View your personalized dashboard with statistics
3. **Create Ticket**: Click "Create New Ticket" or navigate to `/tickets/create`
4. **Fill Details**: 
   - Subject: Clear, descriptive title
   - Department: Select the appropriate office
   - Priority: Choose urgency level
   - Description: Detailed explanation of your issue
   - Attachments: Upload supporting files (optional)

### Managing Your Tickets

**View All Tickets**
- Navigate to "Tickets" in the main navigation
- See ticket status, priority, and creation date
- Click "View" to see full conversation

**Ticket Details Page**
- Full conversation timeline
- All replies and attachments
- Current status and assignment information
- Reply form to add new messages

**Adding Replies**
- Use the reply form at the bottom of ticket details
- Attach additional files if needed
- Replies are instantly visible to support staff

### Email Integration

**Email Notifications**
- Receive email notifications for new tickets and replies
- Reply directly to email notifications to add comments
- Email threading keeps conversations organized

**Creating Tickets via Email**
- Send emails to `support@yourdomain.com` to create new tickets
- System automatically creates your user account if needed
- Email attachments are preserved and accessible in the web interface

**Replying via Email**
- Reply to any ticket notification email
- System automatically adds your reply to the correct ticket
- No need to log into the web interface

### Using the FAQ System

**Browsing FAQs**
- Visit `/faq` to browse frequently asked questions
- Search by keywords across questions and answers
- Filter by department for specific topics
- Each FAQ includes detailed answers and relevant links

**Before Creating a Ticket**
- Check the FAQ page first for quick answers
- Use the search function to find specific topics
- FAQ integration suggests relevant articles during ticket creation

## Agent Guide

### Dashboard Overview

**Agent Dashboard Features**
- Total tickets in your assigned offices
- Tickets assigned specifically to you
- Unassigned tickets requiring attention
- Recent ticket activity with quick action links

**Statistics Widgets**
- Tickets by status (Open, In Progress, Closed, etc.)
- Tickets by priority (High, Medium, Low)
- Your personal workload metrics
- Office-wide statistics

### Managing Tickets

**Accessing Tickets**
- Use the Admin Panel at `/admin`
- Navigate to "Tickets" in the main navigation
- View tickets from your assigned offices only

**Ticket Filters**
- Filter by status, priority, office, or assignee
- Search by subject, content, or customer details
- Sort by creation date, last update, or priority

**Bulk Actions**
- Select multiple tickets for bulk operations
- Change status for multiple tickets at once
- Bulk assignment to agents
- Mass priority updates

**Individual Ticket Management**
- View complete ticket timeline and conversation
- Change status, priority, and assignment
- Add internal notes and customer replies
- Update ticket assignments with automatic notifications

### Communication

**Reply to Customers**
- Use the admin panel to view and reply to tickets
- Add internal notes visible only to staff
- Attach files and documentation
- All replies trigger automatic email notifications

**Email Integration**
- Receive notifications for new tickets in your offices
- Reply to ticket emails to add responses
- System maintains proper email threading
- All email activity is logged in ticket timeline

### Assignment System

**Automatic Assignment**
- New tickets are automatically assigned using round-robin logic
- Assignment considers agent workload and office membership
- Manual reassignment is always possible

**Manual Assignment**
- Reassign tickets to other agents in the same office
- Unassign tickets to return them to the queue
- Assignment changes are logged in ticket timeline

## Admin Guide

### System Administration

**User Management**
- Create, edit, and manage user accounts
- Assign roles: Customer, Agent, or Admin
- Manage office assignments for agents
- Reset passwords and update user information

**Office Configuration**
- Create and manage departments/offices
- Assign agents to specific offices
- Configure office-specific settings
- Set up office hierarchies if needed

**System Settings**
- Manage ticket statuses (Open, Closed, In Progress, etc.)
- Configure priority levels (High, Medium, Low)
- Set default values for new tickets
- Customize color schemes and labels

### Content Management

**FAQ Administration**
- Create, edit, and publish FAQ articles
- Organize FAQs by department or topic
- Set display order and visibility
- Review FAQ usage statistics

**Email Templates**
- Customize notification email templates
- Configure email signatures and branding
- Set up automated responses
- Manage email domains and routing

### Reporting and Analytics

**Dashboard Metrics**
- System-wide ticket statistics
- Agent performance metrics
- Customer satisfaction indicators
- Response time analytics

**Ticket Analytics**
- Track ticket volume trends
- Monitor resolution times
- Analyze common issues and topics
- Generate reports for management

### Advanced Features

**Email-to-Ticket System**
- Configure email domains for ticket creation
- Set up email routing and filtering
- Monitor email processing logs
- Troubleshoot email delivery issues

**Notification Management**
- Configure who receives notifications for different events
- Set up escalation rules for overdue tickets
- Customize notification content and timing
- Manage notification preferences per user

## FAQ System Usage

### For Customers

**Finding Answers**
- Browse categories relevant to your issue
- Use the search function for specific keywords
- Filter by department if you know the relevant area
- Check FAQ before creating tickets for faster resolution

**FAQ Features**
- Expandable question/answer format
- Department-specific FAQs
- General FAQs applicable to all users
- Integration with ticket creation flow

### For Administrators

**Managing FAQs**
- Access FAQ management through Admin Panel
- Create new FAQs with rich text editing
- Organize by department or keep general
- Set publication status and display order

**FAQ Analytics**
- Monitor which FAQs are accessed most frequently
- Track search terms and user behavior
- Identify gaps in knowledge base
- Update content based on common ticket topics

## Email-to-Ticket Workflow

### Customer Experience

1. **Send Email**: Send to `support@yourdomain.com`
2. **Automatic Processing**: System creates ticket and user account
3. **Confirmation**: Receive email confirmation with ticket number
4. **Conversation**: Reply to emails to continue the conversation
5. **Resolution**: Receive notification when ticket is resolved

### Agent Experience

1. **Notification**: Receive email about new ticket
2. **Assignment**: Ticket automatically assigned or available in queue
3. **Response**: Reply via admin panel or email
4. **Tracking**: Monitor conversation in web interface
5. **Resolution**: Update status and close ticket when complete

### Email Address Patterns

- **New Tickets**: `support@yourdomain.com`
- **Replies**: `support+{ticket-uuid}@yourdomain.com`
- **Threading**: System automatically maintains conversation threads

## Troubleshooting

### Common Issues

**Cannot Login**
- Verify email address and password
- Check if account needs activation
- Contact administrator for password reset

**Cannot Create Tickets**
- Ensure you're logged in as a customer
- Check if all required fields are completed
- Verify file attachment size limits

**Not Receiving Email Notifications**
- Check spam/junk folder
- Verify email address in profile
- Contact administrator to check notification settings

**Cannot Access Admin Panel**
- Verify you have Agent or Admin role
- Check with administrator for proper permissions
- Ensure you're accessing the correct URL

### Getting Help

**Contact Support**
- Create a ticket through the web interface
- Send email to support address
- Contact system administrator directly
- Check FAQ for common solutions

**System Status**
- Monitor system announcements
- Check service status page
- Review maintenance schedules
- Follow official communication channels

This comprehensive guide covers all aspects of using TIKM effectively. For development and setup instructions, refer to the main CLAUDE.md file and technical documentation in the docs/ directory.