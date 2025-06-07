# GitHub Webhook Setup Guide

This guide explains how to configure GitHub webhooks for automated deployments of TIKM.

## Overview

The webhook system allows TIKM to automatically deploy updates when code is pushed to the repository. It performs the following tasks:

1. Validates the webhook signature from GitHub
2. Pulls the latest code
3. Installs/updates dependencies
4. Builds frontend assets
5. Runs database migrations
6. Clears and rebuilds caches

## Prerequisites

- Web server with PHP 8.3+
- Git installed and accessible
- Composer installed
- Node.js and npm installed
- Proper file permissions for the web server user

## Configuration Steps

### 1. Environment Setup

Add the following variables to your `.env` file:

```bash
# GitHub Webhook Configuration
GITHUB_WEBHOOK_SECRET=your-secure-webhook-secret-here
GIT_REPO_PATH=/path/to/your/tikm/installation
```

- `GITHUB_WEBHOOK_SECRET`: A secure secret string that GitHub will use to sign webhook payloads
- `GIT_REPO_PATH`: The absolute path to your TIKM installation directory

### 2. GitHub Repository Settings

1. Go to your repository on GitHub
2. Navigate to Settings → Webhooks
3. Click "Add webhook"
4. Configure the webhook:
   - **Payload URL**: `https://your-domain.com/github_webhook.php`
   - **Content type**: `application/json`
   - **Secret**: Use the same value as `GITHUB_WEBHOOK_SECRET` in your `.env`
   - **SSL verification**: Enable (recommended)
   - **Which events?**: Select "Just the push event"
   - **Active**: Check this box

### 3. File Permissions

Ensure the web server user has proper permissions:

```bash
# Make storage directory writable
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Ensure the web server user owns the files
chown -R www-data:www-data /path/to/tikm

# Create log file if it doesn't exist
touch storage/logs/github_webhook.log
chmod 664 storage/logs/github_webhook.log
```

### 4. Testing the Webhook

After configuration:

1. Make a small change to your repository
2. Push the change to GitHub
3. Check the webhook delivery status in GitHub (Settings → Webhooks → Recent Deliveries)
4. Check the log file for details: `tail -f storage/logs/github_webhook.log`

## Security Features

The webhook handler includes several security measures:

- **Signature Verification**: Validates that requests come from GitHub using HMAC-SHA256/SHA1
- **User-Agent Check**: Ensures requests come from GitHub's webhook service
- **Event Filtering**: Only processes push events
- **Direct Access Prevention**: Blocks direct browser access

## Troubleshooting

### Common Issues

1. **403 Forbidden Error**
   - Check that the webhook secret matches in both GitHub and `.env`
   - Verify GitHub's User-Agent is not being blocked by your server

2. **500 Internal Server Error**
   - Check file permissions
   - Ensure all required binaries (git, composer, npm) are accessible
   - Check PHP error logs

3. **Git Pull Fails**
   - Ensure the web server user has SSH keys configured for GitHub access
   - Or use HTTPS with credentials stored in git config

4. **Build Failures**
   - Verify Node.js and npm are in the PATH
   - Check that all npm dependencies are properly installed
   - Review the webhook log for specific error messages

### Log Locations

- Webhook log: `storage/logs/github_webhook.log`
- Laravel log: `storage/logs/laravel.log`
- Web server error log: Check your server configuration

## Manual Deployment

If you need to run deployment tasks manually:

```bash
# Pull latest code
git pull origin main

# Install/update dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Run Laravel optimizations
php artisan migrate --force
php artisan config:cache
php artisan view:cache
php artisan route:cache

# Set permissions
chmod -R 755 storage bootstrap/cache
```

## Customization

The webhook handler is located at `public/github_webhook.php`. You can customize:

- Post-deployment commands
- Environment-specific tasks
- Notification systems
- Additional security checks

## Best Practices

1. **Use Strong Secrets**: Generate a secure webhook secret using:
   ```bash
   openssl rand -base64 32
   ```

2. **Monitor Logs**: Regularly check webhook logs for failed deployments

3. **Test in Staging**: Test webhook configuration in a staging environment first

4. **Backup Before Deploy**: Consider adding backup commands before deployment

5. **Rate Limiting**: Consider implementing rate limiting to prevent abuse

## Additional Resources

- [GitHub Webhooks Documentation](https://docs.github.com/en/developers/webhooks-and-events/webhooks)
- [Securing Webhooks](https://docs.github.com/en/developers/webhooks-and-events/webhooks/securing-your-webhooks)
- [TIKM Deployment Guide](DEPLOYMENT_GUIDE.md)