import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

/**
 * Laravel Echo + Reverb WebSocket Configuration
 * Real-time event broadcasting for ticket updates
 */
if (import.meta.env.VITE_REVERB_APP_KEY && import.meta.env.VITE_REVERB_HOST) {
    Promise.all([
        import('laravel-echo'),
        import('pusher-js')
    ]).then(([{ default: Echo }, { default: Pusher }]) => {
        window.Pusher = Pusher;
        
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
            enabledTransports: ['ws', 'wss'],
            enableLogging: import.meta.env.DEV,
        });
        
        console.log('Laravel Echo initialized with Reverb WebSocket support');
    }).catch(error => {
        console.warn('Failed to initialize Laravel Echo:', error);
        console.info('WebSocket features will be unavailable');
    });
} else {
    console.info('Laravel Echo not initialized - Reverb configuration missing from environment');
}
