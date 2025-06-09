/**
 * Real-time ticket updates using Laravel Echo and Reverb WebSockets
 */

class TicketRealTime {
    constructor() {
        this.currentTicket = null;
        this.currentUser = null;
        this.notificationContainer = null;
        this.init();
    }

    init() {
        // Get current user and ticket information from the page
        this.getCurrentUserAndTicket();
        
        // Create notification container
        this.createNotificationContainer();
        
        // Set up Echo listeners
        this.setupEchoListeners();
    }

    getCurrentUserAndTicket() {
        // Get user info from meta tags or page data
        const userMeta = document.querySelector('meta[name="user-id"]');
        const ticketMeta = document.querySelector('meta[name="ticket-uuid"]');
        
        if (userMeta) {
            this.currentUser = {
                id: parseInt(userMeta.content),
                role: document.querySelector('meta[name="user-role"]')?.content
            };
        }
        
        if (ticketMeta) {
            this.currentTicket = {
                uuid: ticketMeta.content
            };
        }
    }

    createNotificationContainer() {
        // Create a floating notification container
        this.notificationContainer = document.createElement('div');
        this.notificationContainer.id = 'realtime-notifications';
        this.notificationContainer.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(this.notificationContainer);
    }

    setupEchoListeners() {
        if (!window.Echo) {
            console.error('Laravel Echo is not available');
            return;
        }

        // Listen to user-specific channel for all tickets
        if (this.currentUser) {
            this.listenToUserChannel();
        }

        // Listen to ticket-specific channel if viewing a ticket
        if (this.currentTicket) {
            this.listenToTicketChannel();
        }

        // Listen to office channel if user is an agent/admin
        if (this.currentUser && ['agent', 'admin'].includes(this.currentUser.role)) {
            this.listenToOfficeChannels();
        }
    }

    listenToUserChannel() {
        window.Echo.private(`user.${this.currentUser.id}`)
            .listen('.ticket.updated', (e) => {
                this.handleTicketUpdate(e);
            })
            .listen('.ticket.reply.created', (e) => {
                this.handleNewReply(e);
            })
            .listen('.ticket.status.changed', (e) => {
                this.handleStatusChange(e);
            });
    }

    listenToTicketChannel() {
        window.Echo.private(`tickets.${this.currentTicket.uuid}`)
            .listen('.ticket.updated', (e) => {
                this.handleTicketUpdate(e);
            })
            .listen('.ticket.reply.created', (e) => {
                this.handleNewReply(e);
            })
            .listen('.ticket.status.changed', (e) => {
                this.handleStatusChange(e);
            });
    }

    listenToOfficeChannels() {
        // This would require knowing which offices the user belongs to
        // For now, we'll rely on user-specific channels
    }

    handleTicketUpdate(event) {
        console.log('Ticket updated:', event);
        
        // Show notification
        this.showNotification('info', `Ticket "${event.ticket.subject}" was updated by ${event.updated_by.name}`);
        
        // Update UI if on the same ticket page
        if (this.currentTicket && this.currentTicket.uuid === event.ticket.uuid) {
            this.updateTicketStatus(event.ticket);
        }
    }

    handleNewReply(event) {
        console.log('New reply:', event);
        
        // Don't show notification for own replies
        if (event.reply.user.id === this.currentUser.id) {
            return;
        }

        // Don't show internal notes to customers
        if (event.reply.is_internal && this.currentUser.role === 'customer') {
            return;
        }

        const replyType = event.reply.is_internal ? 'internal note' : 'reply';
        this.showNotification('success', `New ${replyType} added to "${event.ticket.subject}" by ${event.reply.user.name}`);
        
        // Add reply to UI if on the same ticket page
        if (this.currentTicket && this.currentTicket.uuid === event.ticket.uuid) {
            this.addReplyToUI(event.reply);
        }
    }

    handleStatusChange(event) {
        console.log('Status changed:', event);
        
        this.showNotification('warning', 
            `Ticket "${event.ticket.subject}" status changed from ${event.status_change.from.name} to ${event.status_change.to.name} by ${event.changed_by.name}`
        );
        
        // Update status in UI if on the same ticket page
        if (this.currentTicket && this.currentTicket.uuid === event.ticket.uuid) {
            this.updateTicketStatusDisplay(event.status_change.to);
        }
    }

    showNotification(type, message) {
        const notification = document.createElement('div');
        notification.className = `p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 ease-in-out ${this.getNotificationClasses(type)}`;
        
        notification.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    ${this.getNotificationIcon(type)}
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">${message}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `;
        
        this.notificationContainer.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    getNotificationClasses(type) {
        const classes = {
            'info': 'bg-blue-50 border border-blue-200 text-blue-800',
            'success': 'bg-green-50 border border-green-200 text-green-800',
            'warning': 'bg-yellow-50 border border-yellow-200 text-yellow-800',
            'error': 'bg-red-50 border border-red-200 text-red-800'
        };
        return classes[type] || classes.info;
    }

    getNotificationIcon(type) {
        const icons = {
            'info': '<svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>',
            'success': '<svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>',
            'warning': '<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>',
            'error': '<svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>'
        };
        return icons[type] || icons.info;
    }

    updateTicketStatus(ticket) {
        // Update ticket status in the UI
        const statusElement = document.querySelector('.ticket-status');
        if (statusElement) {
            statusElement.textContent = ticket.status;
            statusElement.className = `ticket-status badge badge-${ticket.status.toLowerCase()}`;
        }

        // Update last updated time
        const updatedElement = document.querySelector('.ticket-updated-time');
        if (updatedElement) {
            updatedElement.textContent = new Date(ticket.updated_at).toLocaleString();
        }
    }

    updateTicketStatusDisplay(status) {
        const statusElement = document.querySelector('.ticket-status');
        if (statusElement) {
            statusElement.textContent = status.name;
            statusElement.style.backgroundColor = status.color;
        }
    }

    addReplyToUI(reply) {
        const repliesContainer = document.querySelector('.replies-container');
        if (!repliesContainer) return;

        const replyElement = document.createElement('div');
        replyElement.className = `reply-item p-4 rounded-lg mb-4 ${reply.is_internal ? 'bg-yellow-50 border-l-4 border-yellow-400' : 'bg-white border border-gray-200'}`;
        
        replyElement.innerHTML = `
            <div class="flex items-start space-x-3">
                <img src="${reply.user.avatar_url}" alt="${reply.user.name}" class="w-8 h-8 rounded-full">
                <div class="flex-1">
                    <div class="flex items-center space-x-2">
                        <span class="font-medium text-gray-900">${reply.user.name}</span>
                        <span class="text-sm text-gray-500">${reply.user.role}</span>
                        ${reply.is_internal ? '<span class="text-xs bg-yellow-200 text-yellow-800 px-2 py-1 rounded">Internal Note</span>' : ''}
                        <span class="text-sm text-gray-500">${new Date(reply.created_at).toLocaleString()}</span>
                    </div>
                    <div class="mt-2 text-gray-700">${reply.message}</div>
                    ${reply.attachments.length > 0 ? `
                        <div class="mt-2">
                            <strong>Attachments:</strong>
                            ${reply.attachments.map(att => `<span class="inline-block bg-gray-100 px-2 py-1 rounded text-sm mr-2">${att.filename}</span>`).join('')}
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
        
        repliesContainer.appendChild(replyElement);
        
        // Scroll to new reply
        replyElement.scrollIntoView({ behavior: 'smooth' });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize on ticket pages or pages that need real-time updates
    if (document.querySelector('meta[name="ticket-uuid"]') || 
        document.querySelector('.tickets-list') ||
        document.querySelector('#ticket-page')) {
        new TicketRealTime();
    }
});

// Export for manual initialization if needed
window.TicketRealTime = TicketRealTime;