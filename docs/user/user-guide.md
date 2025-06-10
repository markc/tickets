---
title: "User Guide"
description: "Complete guide for customers, agents, and administrators"
order: 2
category: "user"
version: "1.0"
last_updated: "2025-06-10"
---

# User Guide

This comprehensive guide covers how to use TIKM from customer, agent, and administrator perspectives.

## Customer Guide

### Getting Started

As a customer, TIKM provides an intuitive interface for managing your support requests.

#### Creating Your Account

1. **Visit Registration**: Navigate to `/register`
2. **Fill Details**: Provide name, email, and password
3. **Email Verification**: Check your email and verify your account
4. **First Login**: Access your personalized dashboard

#### Dashboard Overview

Your dashboard displays:
- **Ticket Statistics**: Open, in progress, and closed tickets
- **Recent Activity**: Latest ticket updates and replies
- **Quick Actions**: Create new ticket, view all tickets
- **FAQ Search**: Quick access to knowledge base

### Managing Tickets

#### Creating a New Ticket

1. **Navigate**: Click "Create New Ticket" or visit `/tickets/create`
2. **Fill Required Fields**:
   - **Subject**: Clear, descriptive title (required)
   - **Department**: Select appropriate office/department
   - **Priority**: Low, Medium, High, or Urgent
   - **Description**: Detailed explanation of your issue
3. **Add Attachments**: Upload supporting files (optional)
   - Supported formats: Images, PDFs, documents
   - Maximum size: 10MB per file
4. **Submit**: Click "Create Ticket"

#### Viewing Your Tickets

- **All Tickets**: Visit `/tickets` to see complete list
- **Ticket Details**: Click any ticket to view full conversation
- **Status Indicators**: Color-coded status badges
- **Search & Filter**: Find tickets by subject, status, or date

#### Responding to Tickets

1. **Open Ticket**: Click on any ticket from your list
2. **Scroll to Reply**: Find the reply section at bottom
3. **Type Response**: Add your message or additional information
4. **Attach Files**: Include additional supporting materials
5. **Submit Reply**: Your response notifies the assigned agent

#### Ticket Statuses

| Status | Description | Your Action |
|--------|-------------|-------------|
| **Open** | Newly created, awaiting agent | Wait for response |
| **In Progress** | Agent is working on it | Provide additional info if requested |
| **On Hold** | Waiting for external factors | Check for updates regularly |
| **Closed** | Issue resolved | Rate service (optional) |

### Self-Service Options

#### FAQ System

- **Browse Categories**: View FAQs by department
- **Search Knowledge Base**: Use search to find relevant articles
- **Rate Articles**: Help improve content quality
- **Request Articles**: Suggest new FAQ topics

#### Account Management

- **Profile Settings**: Update name, email, password
- **Notification Preferences**: Choose email notification settings
- **View History**: See all your past interactions

## Agent Guide

### Getting Started as an Agent

Agents handle customer support through the admin panel with powerful tools for efficient ticket management.

#### Accessing the Admin Panel

1. **Login**: Use agent credentials at `/login`
2. **Admin Access**: Navigate to `/admin`
3. **Dashboard**: View your assigned tickets and workload
4. **Office Assignment**: Work only with tickets from your assigned offices

#### Agent Dashboard

Your dashboard shows:
- **Assigned Tickets**: Tickets specifically assigned to you
- **Office Tickets**: All tickets in your assigned offices
- **Performance Metrics**: Response times, resolution rates
- **Quick Actions**: Common workflows and bulk operations

### Ticket Management

#### Viewing Tickets

- **Ticket List**: Filter by status, priority, office, or assignment
- **Search Function**: Find tickets by subject, customer, or content
- **Bulk Actions**: Handle multiple tickets simultaneously
- **Advanced Filters**: Date ranges, custom criteria

#### Responding to Customers

1. **Open Ticket**: Click ticket from list or dashboard
2. **Review History**: Read entire conversation thread
3. **Check Timeline**: See all activities and status changes
4. **Add Reply**: 
   - **Public Reply**: Customer can see this
   - **Internal Note**: Only staff can see this
5. **Update Status**: Change ticket status as appropriate
6. **Assign/Reassign**: Transfer to another agent if needed

#### Internal Notes

- **Private Communication**: Add notes only visible to staff
- **Collaboration**: Communicate with other agents
- **Documentation**: Record important details or decisions
- **Handoff Notes**: Useful when reassigning tickets

#### Bulk Operations

- **Select Multiple**: Use checkboxes to select tickets
- **Bulk Status Change**: Update status for multiple tickets
- **Bulk Assignment**: Assign multiple tickets to agents
- **Export Data**: Generate reports or data exports

### Workflow Best Practices

#### Response Time Guidelines

- **High Priority**: Respond within 1 hour
- **Medium Priority**: Respond within 4 hours
- **Low Priority**: Respond within 24 hours
- **Follow-up**: Check tickets daily for customer responses

#### Communication Tips

- **Be Professional**: Maintain courteous, helpful tone
- **Be Clear**: Use simple language, avoid technical jargon
- **Be Thorough**: Address all customer concerns in one response
- **Follow Up**: Check if additional help is needed

## Administrator Guide

### System Administration

Administrators have full control over TIKM configuration and user management.

#### User Management

##### Creating Users

1. **Navigate**: Admin Panel → Users → Create User
2. **User Details**:
   - **Name**: Full name
   - **Email**: Unique email address
   - **Role**: Customer, Agent, or Administrator
   - **Password**: Initial password (user can change)
3. **Office Assignment**: Assign agents to specific offices
4. **Save**: User receives welcome email

##### Managing Roles

- **Customer**: Can create and manage their own tickets
- **Agent**: Can handle tickets from assigned offices
- **Administrator**: Full system access and configuration

##### User Actions

- **Edit Users**: Update details, roles, office assignments
- **Deactivate**: Temporarily disable user access
- **Delete**: Permanently remove user (use with caution)
- **Reset Password**: Generate new password for user

#### Office Configuration

##### Creating Offices

1. **Navigate**: Admin Panel → Offices → Create Office
2. **Office Details**:
   - **Name**: Department name (e.g., "Technical Support")
   - **Description**: Purpose and responsibilities
   - **Internal/External**: Visibility to customers
3. **Agent Assignment**: Add agents to this office
4. **Save**: Office available for ticket routing

##### Office Types

- **External Offices**: Visible to customers in ticket creation
  - Examples: "Technical Support", "Sales", "Customer Service"
- **Internal Offices**: Staff-only for internal workflows
  - Examples: "Development Team", "Management"

#### System Configuration

##### Ticket Priorities

- **Manage Priorities**: Create custom priority levels
- **Color Coding**: Assign colors for visual identification
- **Default Priority**: Set system-wide default

##### Ticket Statuses

- **Custom Statuses**: Create workflow-specific statuses
- **Status Flow**: Define logical status progressions
- **Automation**: Set up automatic status changes

##### Canned Responses

- **Create Templates**: Pre-written responses for common issues
- **Categories**: Organize by topic or office
- **Variables**: Use placeholders for personalization
- **Agent Access**: Make available to specific offices

#### Analytics and Reporting

##### Dashboard Metrics

- **Ticket Volume**: Track ticket creation trends
- **Response Times**: Monitor agent performance
- **Resolution Rates**: Measure customer satisfaction
- **Office Performance**: Compare department efficiency

##### Custom Reports

- **Date Ranges**: Generate reports for specific periods
- **Filter Options**: By office, agent, status, priority
- **Export Options**: PDF, Excel, CSV formats
- **Scheduled Reports**: Automatic email delivery

### Advanced Configuration

#### Email Integration

- **SMTP Setup**: Configure outgoing email server
- **Email Templates**: Customize notification messages
- **Email Routing**: Set up email-to-ticket creation
- **Threading**: Ensure proper conversation threading

#### Security Settings

- **Access Control**: Configure role-based permissions
- **Session Management**: Set timeout and security policies
- **File Upload**: Configure allowed file types and sizes
- **Audit Logs**: Track administrative actions

#### Performance Optimization

- **Database Tuning**: Optimize for large ticket volumes
- **Cache Configuration**: Improve response times
- **Queue Workers**: Handle background job processing
- **Search Indexing**: Optimize ticket and FAQ search

## Tips and Best Practices

### For All Users

- **Keep Information Updated**: Ensure profile information is current
- **Use Clear Communication**: Write clear, concise messages
- **Attach Relevant Files**: Include screenshots or logs when helpful
- **Follow Up**: Check for responses and provide additional information

### For Customers

- **Search First**: Check FAQs before creating tickets
- **Be Specific**: Provide detailed problem descriptions
- **Include Context**: Mention what you were trying to do
- **Be Patient**: Allow reasonable time for agent responses

### For Agents

- **Stay Organized**: Use internal notes for documentation
- **Be Proactive**: Follow up on pending tickets
- **Collaborate**: Use internal notes to communicate with team
- **Update Status**: Keep ticket status current

### For Administrators

- **Regular Monitoring**: Check system health and performance
- **User Training**: Ensure users understand their roles
- **Content Management**: Keep FAQs and templates updated
- **Security Reviews**: Regularly review access and permissions

## Getting Help

If you need assistance with TIKM:

- **Documentation**: Check relevant sections in this guide
- **FAQ System**: Search the knowledge base
- **Support Ticket**: Create a ticket for technical issues
- **Contact Admin**: Reach out to your system administrator

---

**Next**: [FAQ System Guide](faq-system.md) - Learn about the knowledge base