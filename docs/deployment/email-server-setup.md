# Email-to-Ticket Server Configuration Guide

This guide provides step-by-step instructions for configuring a local Postfix/Dovecot mail server to process incoming emails and automatically create tickets or replies in TIKM.

## Overview

The email-to-ticket system works by:
1. Configuring Dovecot with Sieve to filter incoming emails
2. Using a shell script to pipe emails to Laravel
3. Processing emails with the `ticket:process-email` Artisan command
4. Creating tickets or replies based on email content

## Prerequisites

- Ubuntu/Debian server with root access
- Laravel application installed and configured
- Basic understanding of mail server administration

## 1. Install Required Packages

```bash
# Update package manager
sudo apt update

# Install Postfix and Dovecot
sudo apt install postfix dovecot-core dovecot-imapd dovecot-pop3d dovecot-lmtpd

# Install Sieve plugin for Dovecot
sudo apt install dovecot-sieve dovecot-managesieved

# Install additional tools
sudo apt install mailutils mutt
```

## 2. Configure Postfix

### A. Main Configuration (`/etc/postfix/main.cf`)

Add or modify these settings:

```bash
# Basic configuration
myhostname = your-server.domain.com
mydomain = domain.com
myorigin = $mydomain
inet_interfaces = all
mydestination = $myhostname, localhost.$mydomain, localhost, $mydomain

# Mailbox configuration
home_mailbox = Maildir/
mailbox_command = 

# Virtual aliases for support email
virtual_alias_maps = hash:/etc/postfix/virtual

# Delivery to Dovecot
mailbox_transport = lmtp:unix:private/dovecot-lmtp
```

### B. Virtual Aliases (`/etc/postfix/virtual`)

```bash
# Catch-all for support emails
support@domain.com    support@domain.com
support+*@domain.com  support@domain.com

# Build the alias database
sudo postmap /etc/postfix/virtual
```

### C. Master Configuration (`/etc/postfix/master.cf`)

Ensure these lines exist:

```bash
# Dovecot LMTP
dovecot   unix  -       n       n       -       -       pipe
  flags=DRhu user=vmail:vmail argv=/usr/lib/dovecot/deliver -f ${sender} -d ${recipient}
```

## 3. Configure Dovecot

### A. Main Configuration (`/etc/dovecot/dovecot.conf`)

```bash
# Enable required protocols
protocols = imap pop3 lmtp sieve

# Include configuration files
!include conf.d/*.conf
```

### B. Mail Location (`/etc/dovecot/conf.d/10-mail.conf`)

```bash
# Mail location
mail_location = maildir:~/Maildir

# Mail user and group
mail_uid = 1000
mail_gid = 1000

# First valid UID/GID for users
first_valid_uid = 1000
first_valid_gid = 1000
```

### C. Authentication (`/etc/dovecot/conf.d/10-auth.conf`)

```bash
# Enable plain text authentication (for local testing)
disable_plaintext_auth = no

# Authentication mechanisms
auth_mechanisms = plain login

# Include auth system configuration
!include auth-system.conf.ext
```

### D. LMTP Service (`/etc/dovecot/conf.d/20-lmtp.conf`)

```bash
# LMTP configuration
protocol lmtp {
  # Enable Sieve for LMTP
  mail_plugins = $mail_plugins sieve
}
```

### E. Sieve Configuration (`/etc/dovecot/conf.d/90-sieve.conf`)

```bash
plugin {
  # Enable Sieve plugin
  sieve = ~/.dovecot.sieve
  
  # Global Sieve scripts (executed before user scripts)
  sieve_global_path = /etc/dovecot/sieve/default.sieve
  
  # Enable pipe extension for external programs
  sieve_extensions = +vnd.dovecot.pipe
  
  # Directory containing executable scripts
  sieve_pipe_bin_dir = /etc/dovecot/sieve/scripts
  
  # Socket for ManageSieve service
  sieve_global_dir = /etc/dovecot/sieve/global/
}
```

## 4. Create Sieve Scripts

### A. Create Sieve Directories

```bash
sudo mkdir -p /etc/dovecot/sieve/scripts
sudo mkdir -p /etc/dovecot/sieve/global
```

### B. Global Sieve Script (`/etc/dovecot/sieve/default.sieve`)

```sieve
require ["vnd.dovecot.pipe", "fileinto", "regex"];

# Process support emails
if address :regex ["to", "cc"] "support(\\+.*)?@domain\\.com" {
  # Pipe to Laravel processing script
  pipe "process_ticket_email.sh";
  
  # Keep a copy for auditing (optional)
  fileinto "INBOX.ProcessedTickets";
  
  # Stop further processing
  stop;
}

# Default: deliver to INBOX
fileinto "INBOX";
```

### C. Processing Shell Script (`/etc/dovecot/sieve/scripts/process_ticket_email.sh`)

```bash
#!/bin/bash

# Laravel application path
APP_PATH="/var/www/your-laravel-app"

# Log file for debugging
LOG_FILE="/var/log/ticket-email-processing.log"

# Function to log messages
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

# Change to Laravel directory
cd "$APP_PATH" || {
    log_message "ERROR: Could not change to Laravel directory: $APP_PATH"
    exit 1
}

# Create log entry
log_message "Processing incoming email..."

# Execute Laravel command and capture output
/usr/bin/php artisan ticket:process-email 2>&1 | while read line; do
    log_message "Laravel: $line"
done

# Check exit status
if [ ${PIPESTATUS[0]} -eq 0 ]; then
    log_message "Email processed successfully"
    exit 0
else
    log_message "ERROR: Email processing failed with exit code ${PIPESTATUS[0]}"
    exit 1
fi
```

### D. Set Permissions

```bash
# Make script executable
sudo chmod +x /etc/dovecot/sieve/scripts/process_ticket_email.sh

# Compile Sieve script
sudo sievec /etc/dovecot/sieve/default.sieve

# Set ownership
sudo chown -R dovecot:dovecot /etc/dovecot/sieve/

# Create log file with proper permissions
sudo touch /var/log/ticket-email-processing.log
sudo chown dovecot:dovecot /var/log/ticket-email-processing.log
sudo chmod 644 /var/log/ticket-email-processing.log
```

## 5. Laravel Application Configuration

### A. Environment Variables (`.env`)

```bash
# Mail configuration
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=25
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=support@domain.com
MAIL_FROM_NAME="Your App Support"

# Support domain for email-to-ticket
MAIL_SUPPORT_DOMAIN=domain.com

# Queue configuration (recommended for email processing)
QUEUE_CONNECTION=database
```

### B. File Permissions

```bash
# Ensure Laravel can write to storage directories
sudo chown -R www-data:www-data /var/www/your-laravel-app/storage
sudo chmod -R 775 /var/www/your-laravel-app/storage

# Ensure logs directory is writable
sudo chown -R www-data:www-data /var/www/your-laravel-app/storage/logs
```

## 6. Testing the Configuration

### A. Test Email Processing

```bash
# Send a test email to create a ticket
echo "This is a test ticket from email" | mail -s "Test Ticket Subject" support@domain.com

# Send a test reply (replace UUID with actual ticket UUID)
echo "This is a test reply" | mail -s "Re: Test Subject" support+12345678-1234-1234-1234-123456789012@domain.com
```

### B. Debug Issues

```bash
# Check Laravel logs
tail -f /var/www/your-laravel-app/storage/logs/laravel.log

# Check email processing logs
tail -f /var/log/ticket-email-processing.log

# Check Dovecot logs
sudo tail -f /var/log/dovecot.log

# Check Postfix logs
sudo tail -f /var/log/mail.log

# Test Sieve script manually
sudo -u dovecot sieve-test /etc/dovecot/sieve/default.sieve < test-email.eml
```

### C. Manual Testing

```bash
# Test the Laravel command directly with a sample email
cd /var/www/your-laravel-app
echo -e "From: test@example.com\nTo: support@domain.com\nSubject: Test\n\nTest message" | php artisan ticket:process-email
```

## 7. Security Considerations

### A. Firewall Configuration

```bash
# Allow SMTP traffic
sudo ufw allow 25/tcp

# Allow IMAP if needed
sudo ufw allow 143/tcp
sudo ufw allow 993/tcp

# Allow POP3 if needed  
sudo ufw allow 110/tcp
sudo ufw allow 995/tcp
```

### B. Access Controls

```bash
# Limit access to Sieve scripts
sudo chmod 755 /etc/dovecot/sieve/scripts/
sudo chmod 644 /etc/dovecot/sieve/scripts/process_ticket_email.sh
sudo chmod +x /etc/dovecot/sieve/scripts/process_ticket_email.sh
```

### C. Rate Limiting

Add to `/etc/dovecot/conf.d/90-sieve.conf`:

```bash
plugin {
  # ... existing configuration ...
  
  # Limit pipe script execution
  sieve_pipe_socket_dir = /var/run/dovecot/sieve-pipe
  sieve_pipe_timeout = 30
}
```

## 8. Monitoring and Maintenance

### A. Log Rotation

Create `/etc/logrotate.d/ticket-email`:

```bash
/var/log/ticket-email-processing.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    postrotate
        /bin/systemctl reload dovecot.service > /dev/null 2>&1 || true
    endscript
}
```

### B. Service Management

```bash
# Restart services after configuration changes
sudo systemctl restart postfix
sudo systemctl restart dovecot

# Enable services to start on boot
sudo systemctl enable postfix
sudo systemctl enable dovecot

# Check service status
sudo systemctl status postfix
sudo systemctl status dovecot
```

## 9. Troubleshooting Common Issues

### Issue: Emails not being processed

**Check:**
1. Postfix is receiving emails: `sudo tail -f /var/log/mail.log`
2. Dovecot is delivering emails: `sudo tail -f /var/log/dovecot.log`
3. Sieve script is executing: Check log file
4. Laravel command is working: Test manually

### Issue: Permission denied errors

**Solution:**
```bash
# Fix file permissions
sudo chown -R dovecot:dovecot /etc/dovecot/sieve/
sudo chmod +x /etc/dovecot/sieve/scripts/process_ticket_email.sh

# Fix Laravel permissions
sudo chown -R www-data:www-data /var/www/your-laravel-app/storage
```

### Issue: Sieve script not executing

**Check:**
```bash
# Verify Sieve compilation
sudo sievec /etc/dovecot/sieve/default.sieve

# Test Sieve script
sudo -u dovecot sieve-test /etc/dovecot/sieve/default.sieve < test-email.eml
```

## 10. Production Recommendations

1. **Use a dedicated mail server** for production environments
2. **Implement proper DNS records** (MX, SPF, DKIM, DMARC)
3. **Use TLS/SSL encryption** for mail transmission
4. **Monitor mail queues** and processing logs regularly
5. **Implement backup strategies** for email data
6. **Use a message queue system** (Redis/Supervisor) for Laravel
7. **Set up monitoring alerts** for failed email processing

This configuration provides a robust foundation for email-to-ticket functionality while maintaining security and reliability.