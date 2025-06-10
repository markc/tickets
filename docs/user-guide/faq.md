---
title: Frequently Asked Questions
description: Common questions and answers about TIKM
category: user-guide
order: 10
---

# Frequently Asked Questions

This section contains answers to the most commonly asked questions about TIKM.

## General Questions

<details>
<summary>What is TIKM?</summary>

TIKM is a comprehensive customer support and ticketing system built with Laravel and Filament. It's designed to streamline helpdesk operations, manage user inquiries, and provide a robust knowledge base.

Key features include:
- **Ticket Management**: Create, assign, and track support tickets
- **Email Integration**: Automatic ticket creation from incoming emails
- **Knowledge Base**: Searchable FAQ and documentation system
- **Role-based Access**: Different permissions for customers, agents, and admins
- **Real-time Updates**: Live notifications and status updates

</details>

<details>
<summary>What technologies does TIKM use?</summary>

The core stack includes:
- **Backend:** Laravel 12
- **Admin Panel:** Filament 3.3
- **Frontend:** Tailwind CSS 4.0 & Alpine.js
- **Database:** MySQL / PostgreSQL / SQLite
- **Real-time:** Laravel Reverb or Pusher
- **Search:** TntSearch (lightweight) or Laravel Scout
- **Authentication:** Laravel Breeze + Sanctum (API)

</details>

<details>
<summary>Is there a dark mode?</summary>

Yes! The entire TIKM interface, including the documentation, automatically adapts to your system's light or dark mode preference for a comfortable viewing experience.

You can also manually toggle between light and dark themes in the admin panel settings.

</details>

## Installation & Setup

<details>
<summary>What are the system requirements?</summary>

**Minimum Requirements:**
- PHP 8.3 or higher
- Composer 2.0+
- Node.js 18+ and npm
- Database: MySQL 8.0+, PostgreSQL 13+, or SQLite 3.35+

**Recommended:**
- PHP 8.4
- At least 512MB RAM
- SSD storage for better performance

</details>

<details>
<summary>How do I install TIKM?</summary>

Follow these steps for a fresh installation:

```bash
# Clone the repository
git clone https://github.com/your-org/tikm.git
cd tikm

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Build frontend assets
npm run build

# Start the development server
php artisan serve
```

For detailed installation instructions, see the [Deployment Guide](../deployment/index.md).

</details>

<details>
<summary>Can I use TIKM with an existing Laravel application?</summary>

TIKM is designed as a standalone application, but many of its components can be integrated into existing Laravel projects:

- **Models & Migrations**: Copy the ticket-related models and migrations
- **Filament Resources**: Import the admin panel resources
- **Email Processing**: Use the email-to-ticket service
- **API Endpoints**: Integrate the REST API controllers

However, we recommend using TIKM as a dedicated support system for the best experience.

</details>

## Features & Usage

<details>
<summary>How does email-to-ticket conversion work?</summary>

TIKM automatically converts incoming emails into support tickets:

1. **Email Reception**: Configure your mail server to pipe emails to TIKM
2. **Processing**: The system parses the email content, attachments, and headers
3. **Ticket Creation**: A new ticket is created with the email as the initial message
4. **Notifications**: Relevant staff are notified of the new ticket
5. **Reply Threading**: Subsequent emails are added as replies to the existing ticket

The system supports both new ticket creation and reply threading using email addresses like `support+ticket-uuid@domain.com`.

</details>

<details>
<summary>Can customers view their ticket history?</summary>

Yes! Customers have access to a dedicated portal where they can:

- View all their submitted tickets
- Track ticket status and progress
- Add replies and additional information
- Upload attachments
- Search through their ticket history
- Receive real-time notifications

The customer portal is separate from the admin interface and provides a clean, focused experience.

</details>

<details>
<summary>How do I set up the knowledge base?</summary>

The knowledge base system uses Markdown files for easy content management:

1. **Create FAQ entries** using the admin panel
2. **Organize by categories** for easy navigation
3. **Use search functionality** powered by TntSearch
4. **Track usage** to see which articles are most helpful
5. **Link from tickets** to provide quick answers

You can also use the documentation system (like this page) for more detailed guides and tutorials.

</details>

## Troubleshooting

<details>
<summary>Email processing isn't working</summary>

If emails aren't being converted to tickets, check:

1. **Queue Processing**: Ensure `php artisan queue:listen` is running
2. **Mail Configuration**: Verify your `.env` mail settings
3. **Permissions**: Check file permissions on `storage/` directories
4. **Logs**: Review `storage/logs/laravel.log` for errors
5. **Command Testing**: Test email processing manually:

```bash
echo "Test email content" | php artisan ticket:process-email
```

</details>

<details>
<summary>Real-time notifications not working</summary>

For WebSocket/real-time issues:

1. **Reverb Server**: Ensure Laravel Reverb is running:
   ```bash
   php artisan reverb:start
   ```

2. **Environment Variables**: Check your `.env` file:
   ```
   BROADCAST_CONNECTION=reverb
   REVERB_APP_ID=your-app-id
   REVERB_APP_KEY=your-app-key
   REVERB_APP_SECRET=your-app-secret
   ```

3. **Browser Console**: Check for JavaScript errors in the browser console
4. **Firewall**: Ensure WebSocket ports (8080) are open

</details>

<details>
<summary>Performance issues with large ticket volumes</summary>

For better performance with high ticket volumes:

1. **Database Optimization**: Add indexes to frequently queried columns
2. **Queue Processing**: Use Redis or database queues instead of sync
3. **Search Indexing**: Consider upgrading to Laravel Scout with Algolia/Elasticsearch
4. **Caching**: Enable Redis caching for sessions and application cache
5. **Asset Optimization**: Use `npm run build` for production assets

```bash
# Optimize for production
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

</details>

## Still Need Help?

If you can't find the answer to your question here, you can:

- Check the [User Guide](../user-guide/index.md) for detailed documentation
- Review the [API Reference](../api/index.md) for integration details
- Submit a support ticket through the system
- Contact the development team

**Need to add a new FAQ?** Edit this file or create a new FAQ entry through the admin panel.