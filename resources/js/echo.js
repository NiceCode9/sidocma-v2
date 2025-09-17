import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
});

// Connection event handlers
window.Echo.connector.pusher.connection.bind('connected', function () {
    console.log('✅ WebSocket Connected to Reverb server');
    // Update UI to show connected status
    updateConnectionStatus('connected');
});

window.Echo.connector.pusher.connection.bind('disconnected', function () {
    console.log('❌ WebSocket Disconnected from Reverb server');
    // Update UI to show disconnected status
    updateConnectionStatus('disconnected');
});

window.Echo.connector.pusher.connection.bind('connecting', function () {
    console.log('🔄 Connecting to Reverb server...');
    updateConnectionStatus('connecting');
});

window.Echo.connector.pusher.connection.bind('failed', function () {
    console.error('💥 Failed to connect to Reverb server');
    updateConnectionStatus('failed');
});

window.Echo.connector.pusher.connection.bind('error', function (error) {
    console.error('WebSocket Error:', error);
    updateConnectionStatus('error');
});

// Function to update connection status in UI
function updateConnectionStatus(status) {
    const statusElement = document.getElementById('connection-status');
    if (!statusElement) return;

    const statusConfig = {
        connected: {
            class: 'text-success',
            icon: 'fas fa-wifi',
            text: 'Terhubung'
        },
        connecting: {
            class: 'text-warning',
            icon: 'fas fa-spinner fa-spin',
            text: 'Menghubungkan...'
        },
        disconnected: {
            class: 'text-danger',
            icon: 'fas fa-wifi-slash',
            text: 'Terputus'
        },
        failed: {
            class: 'text-danger',
            icon: 'fas fa-exclamation-triangle',
            text: 'Gagal Terhubung'
        },
        error: {
            class: 'text-danger',
            icon: 'fas fa-times-circle',
            text: 'Error'
        }
    };

    const config = statusConfig[status] || statusConfig.disconnected;
    statusElement.className = `connection-status ${config.class}`;
    statusElement.innerHTML = `<i class="${config.icon}"></i> ${config.text}`;
}

// Global error handler untuk development
if (import.meta.env.DEV) {
    window.addEventListener('error', function (event) {
        console.error('Global Error:', event.error);
    });

    window.addEventListener('unhandledrejection', function (event) {
        console.error('Unhandled Promise Rejection:', event.reason);
    });
}

// Utility function untuk debugging di console
window.debugEcho = function () {
    console.log('Echo Instance:', window.Echo);
    console.log('Pusher Connection State:', window.Echo.connector.pusher.connection.state);
    console.log('Active Channels:', Object.keys(window.Echo.connector.channels));
};
