# WebSocket Real-time Updates Implementation

This document provides comprehensive information about the WebSocket real-time updates system implemented in TIKM using Laravel Reverb and Laravel Echo.

## Overview

The WebSocket implementation enables real-time updates for ticket activities, allowing users to see changes immediately without page refreshes. This includes new replies, status changes, assignments, and other ticket modifications.

## Architecture

### Technology Stack
- **Laravel Reverb**: Native PHP WebSocket server (Laravel's first-party solution)
- **Laravel Echo**: JavaScript client library for WebSocket connections
- **Laravel Broadcasting**: Event broadcasting system with channel authorization
- **Private Channels**: Secure, role-based access to real-time updates

### Key Components

#### 1. WebSocket Server (Laravel Reverb)
```bash
# Start the WebSocket server
php artisan reverb:start --port=8080

# Background process (production)
php artisan reverb:start --port=8080 --daemon
```

#### 2. Broadcast Events
Located in `app/Events/`:

- **TicketUpdated**: Triggered when ticket properties change
- **TicketReplyCreated**: Triggered when new replies are added
- **TicketStatusChanged**: Triggered when ticket status changes

#### 3. Client-side Integration
- **Bootstrap Configuration**: `resources/js/bootstrap.js`
- **Real-time Handler**: `resources/js/ticket-realtime.js`
- **UI Integration**: Automatic DOM updates and notifications

## Configuration

### Environment Variables (.env)
```env
BROADCAST_CONNECTION=reverb

# Reverb WebSocket Server Configuration
REVERB_APP_ID=153066
REVERB_APP_KEY=h8yppgg7uutmexzrh2g0
REVERB_APP_SECRET=ly7t4bttpbygojtazps5
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

# Vite Frontend Configuration
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### Laravel Echo Configuration
```javascript
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    enableLogging: import.meta.env.DEV,
});
```

## Channel Authorization

Channels are secured using Laravel's broadcasting authorization system in `routes/channels.php`:

### Channel Types

#### 1. User-specific Channels
```php
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```
- **Purpose**: Personal notifications and updates
- **Access**: Only the specific user

#### 2. Ticket-specific Channels
```php
Broadcast::channel('tickets.{ticketUuid}', function ($user, $ticketUuid) {
    $ticket = Ticket::where('uuid', $ticketUuid)->first();
    if (!$ticket) return false;
    
    // Customers can only access their own tickets
    if ($user->isCustomer()) {
        return $ticket->creator_id === $user->id;
    }
    
    // Agents can access tickets in their offices or assigned to them
    if ($user->isAgent()) {
        return $user->offices->contains($ticket->office_id) || 
               $ticket->assigned_to_id === $user->id ||
               $ticket->creator_id === $user->id;
    }
    
    // Admins can access all tickets
    return $user->isAdmin();
});
```
- **Purpose**: Ticket-specific updates for authorized users
- **Access**: Role-based authorization

#### 3. Office-specific Channels
```php
Broadcast::channel('office.{officeId}', function ($user, $officeId) {
    if ($user->isCustomer()) return false;
    
    if ($user->isAgent()) {
        return $user->offices->contains($officeId);
    }
    
    return $user->isAdmin();
});
```
- **Purpose**: Department-wide notifications
- **Access**: Agents and admins in the office

## Event Broadcasting

### Event Structure

Each broadcast event implements `ShouldBroadcast` and defines:
- **Broadcasting channels**: Where the event should be sent
- **Event data**: Information included in the broadcast
- **Event name**: How the client identifies the event

### Example: TicketReplyCreated Event
```php
class TicketReplyCreated implements ShouldBroadcast
{
    public function __construct(
        public TicketReply $reply,
        public Ticket $ticket
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tickets.' . $this->ticket->uuid),
            new PrivateChannel('user.' . $this->ticket->creator_id),
            new PrivateChannel('office.' . $this->ticket->office_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ticket.reply.created';
    }

    public function broadcastWith(): array
    {
        return [
            'ticket' => [
                'uuid' => $this->ticket->uuid,
                'subject' => $this->ticket->subject,
            ],
            'reply' => [
                'id' => $this->reply->id,
                'message' => $this->reply->message,
                'is_internal' => $this->reply->is_internal,
                'created_at' => $this->reply->created_at,
                'user' => [
                    'id' => $this->reply->user->id,
                    'name' => $this->reply->user->name,
                    'role' => $this->reply->user->role,
                ],
                'attachments' => $this->reply->attachments->map(function ($attachment) {
                    return [
                        'filename' => $attachment->filename,
                        'size' => $attachment->size,
                    ];
                }),
            ],
        ];
    }
}
```

## Client-side Implementation

### Real-time Event Handling

The `TicketRealTime` class handles all WebSocket interactions:

```javascript
class TicketRealTime {
    constructor() {
        this.currentTicket = null;
        this.currentUser = null;
        this.notificationContainer = null;
        this.init();
    }

    setupEchoListeners() {
        // User-specific channel
        if (this.currentUser) {
            this.listenToUserChannel();
        }

        // Ticket-specific channel
        if (this.currentTicket) {
            this.listenToTicketChannel();
        }
    }

    handleNewReply(event) {
        // Don't show notification for own replies
        if (event.reply.user.id === this.currentUser.id) {
            return;
        }

        // Show notification
        this.showNotification('success', 
            `New reply added to "${event.ticket.subject}" by ${event.reply.user.name}`
        );
        
        // Add reply to UI if on the same ticket page
        if (this.currentTicket && this.currentTicket.uuid === event.ticket.uuid) {
            this.addReplyToUI(event.reply);
        }
    }
}
```

### UI Updates

The system automatically updates the UI when events are received:

1. **Notifications**: Floating notifications for all updates
2. **Dynamic Content**: New replies added to the conversation
3. **Status Updates**: Ticket status badges updated in real-time
4. **Visual Feedback**: Smooth animations and transitions

## Development and Testing

### Starting the Development Environment

1. **Start Laravel Reverb Server**:
```bash
php artisan reverb:start --port=8080
```

2. **Start Laravel Development Server**:
```bash
php artisan serve
```

3. **Start Vite Development Server**:
```bash
npm run dev
```

### Testing WebSocket Events

Use Laravel Tinker to manually trigger events:

```php
php artisan tinker

// Test ticket update event
$ticket = App\Models\Ticket::first();
event(new App\Events\TicketUpdated($ticket, $ticket->creator, ['test' => 'websocket']));

// Test reply created event
$reply = App\Models\TicketReply::with(['user', 'ticket'])->first();
event(new App\Events\TicketReplyCreated($reply, $reply->ticket));
```

### Debugging WebSocket Connections

1. **Browser Developer Tools**: Check the Network tab for WebSocket connections
2. **Reverb Logs**: Monitor the Reverb server console output
3. **Laravel Logs**: Check `storage/logs/laravel.log` for broadcasting errors
4. **Echo Debug Mode**: Enable logging in development environment

## Production Deployment

### Process Management

Use a process manager like Supervisor to keep Reverb running:

```ini
[program:reverb]
command=php /path/to/your/app/artisan reverb:start --port=8080
directory=/path/to/your/app
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/your/app/storage/logs/reverb.log
```

### SSL/TLS Configuration

For production with HTTPS:

```env
REVERB_SCHEME=https
REVERB_PORT=443
```

Configure your web server (Nginx/Apache) to proxy WebSocket connections.

### Performance Considerations

1. **Connection Limits**: Monitor concurrent WebSocket connections
2. **Memory Usage**: Reverb memory consumption scales with connections
3. **Channel Cleanup**: Implement proper channel cleanup for inactive users
4. **Load Balancing**: Use Redis adapter for multi-server deployments

## Security Considerations

### Channel Authorization
- All channels use private authentication
- Role-based access control prevents unauthorized access
- Ticket-specific authorization based on user permissions

### Data Filtering
- Internal notes only broadcast to agents/admins
- Customer data filtered based on privacy settings
- Sensitive information excluded from broadcasts

### Rate Limiting
- WebSocket connections respect Laravel's rate limiting
- Event broadcasting includes throttling mechanisms
- Client-side connection retry with backoff

## Troubleshooting

### Common Issues

1. **WebSocket Connection Failed**
   - Check Reverb server is running
   - Verify port is accessible
   - Check firewall settings

2. **Events Not Broadcasting**
   - Verify `BROADCAST_CONNECTION=reverb` in .env
   - Check event implements `ShouldBroadcast`
   - Ensure channels are properly authorized

3. **Client Not Receiving Events**
   - Verify Echo configuration matches Reverb settings
   - Check browser console for JavaScript errors
   - Ensure user is authenticated for private channels

### Debug Commands

```bash
# Check Reverb configuration
php artisan config:show broadcasting

# Test event broadcasting
php artisan tinker
event(new App\Events\TicketUpdated($ticket, $user, []));

# Monitor WebSocket connections
tail -f storage/logs/reverb.log
```

## Future Enhancements

### Potential Improvements

1. **Typing Indicators**: Show when users are typing replies
2. **Online Presence**: Display which users are currently online
3. **Message Delivery Status**: Confirm message delivery to recipients
4. **Desktop Notifications**: Browser push notifications for important updates
5. **Mobile WebSocket**: Optimize for mobile app integration
6. **Performance Metrics**: Monitor WebSocket connection health

### Scalability Options

1. **Redis Adapter**: Use Redis for multi-server broadcasting
2. **Horizontal Scaling**: Load balance Reverb servers
3. **CDN Integration**: Use CDN for WebSocket connections
4. **Clustering**: Implement WebSocket server clustering

## Conclusion

The WebSocket implementation provides a robust foundation for real-time collaboration in the TIKM support system. It enables immediate updates across all connected clients while maintaining security and performance standards suitable for production use.

The system is designed to be:
- **Secure**: Role-based channel authorization
- **Scalable**: Efficient event broadcasting and connection management  
- **Maintainable**: Clean separation of concerns and comprehensive documentation
- **User-friendly**: Smooth UI updates with clear notifications

For additional support or questions about the WebSocket implementation, refer to the Laravel Reverb documentation or the project's technical documentation.