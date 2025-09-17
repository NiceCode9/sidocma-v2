import Echo from "laravel-echo";
import Pusher from "pusher-js";

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


class NotificationManager {
    constructor() {
        this.notifications = [];
        this.unreadCount = 0;
        this.initializeListeners();
        this.setupUI();
    }

    setupUI() {
        // Create notification container if not exists
        if (!document.getElementById('notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'notification-container';
            container.innerHTML = `
                <div class="notification-bell" onclick="toggleNotifications()">
                    <i class="fas fa-bell"></i>
                    <span id="notification-count" class="notification-count" style="display: none;">0</span>
                </div>
                <div id="notification-dropdown" class="notification-dropdown" style="display: none;">
                    <div class="notification-header">
                        <h4>Notifikasi Surat</h4>
                        <button onclick="markAllAsRead()" class="mark-all-read">Tandai Semua Dibaca</button>
                    </div>
                    <div id="notification-list" class="notification-list">
                        <div class="empty-notifications">Tidak ada notifikasi baru</div>
                    </div>
                </div>
            `;
            document.body.appendChild(container);
        }
    }

    initializeListeners() {
        // Listen top hospital-wide notifications
        window.Echo.channel('hospital-notifications')
            .listen('.surat.created', (e) => {
                this.addNotification({
                    id: e.id,
                    type: 'surat_created',
                    title: 'Surat Baru Diterima',
                    message: `${e.perihal} - dari ${e.user}`,
                    data: e,
                    timestamp: new Date()
                });
                this.playNotificationSound();
            })
            .listen('.surat.read', (e) => {
                this.addNotification({
                    id: e.id,
                    type: 'surat_read',
                    title: 'Surat Telah Dibaca',
                    message: `${e.perihal} - dibaca oleh ${e.opened_by}`,
                    data: e,
                    timestamp: new Date()
                });
            });

        // Listen to user-specific notifications if user is authenticated
        if (window.Laravel && window.Laravel.user) {
            window.Echo.private(`user.${window.Laravel.user.id}`)
                .listen('.surat.created', (e) => {
                    this.addNotification({
                        id: e.id,
                        type: 'surat_created_personal',
                        title: 'Surat Ditujukan untuk Anda',
                        message: `${e.perihal}`,
                        data: e,
                        timestamp: new Date(),
                        priority: true
                    });
                    this.playNotificationSound();
                    this.showToastNotification('Surat baru ditujukan untuk Anda!');
                });
        }
    }

    addNotification(notification) {
        this.notifications.unshift(notification);
        this.unreadCount++;
        this.updateUI();

        // Keep only last 50 notifications
        if (this.notifications.length > 50) {
            this.notifications = this.notifications.slice(0, 50);
        }
    }

    updateUI() {
        const countElement = document.getElementById('notification-count');
        const listElement = document.getElementById('notification-list');

        if (countElement) {
            countElement.textContent = this.unreadCount;
            countElement.style.display = this.unreadCount > 0 ? 'block' : 'none';
        }

        if (listElement) {
            if (this.notifications.length === 0) {
                listElement.innerHTML = '<div class="empty-notifications">Tidak ada notifikasi baru</div>';
            } else {
                listElement.innerHTML = this.notifications.map(notification => `
                    <div class="notification-item ${notification.priority ? 'priority' : ''}" data-id="${notification.id}">
                        <div class="notification-icon">
                            <i class="fas ${this.getNotificationIcon(notification.type)}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">${notification.title}</div>
                            <div class="notification-message">${notification.message}</div>
                            <div class="notification-time">${this.formatTime(notification.timestamp)}</div>
                        </div>
                        ${notification.type.includes('surat_created') ?
                        `<button onclick="viewSurat('${notification.data.no_surat}')" class="view-btn">Lihat</button>` :
                        ''
                    }
                    </div>
                `).join('');
            }
        }
    }

    getNotificationIcon(type) {
        switch (type) {
            case 'surat_created':
            case 'surat_created_personal':
                return 'fa-envelope';
            case 'surat_read':
                return 'fa-envelope-open';
            default:
                return 'fa-info-circle';
        }
    }

    formatTime(timestamp) {
        const now = new Date();
        const diff = now - timestamp;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);

        if (minutes < 1) return 'Baru saja';
        if (minutes < 60) return `${minutes} menit yang lalu`;
        if (hours < 24) return `${hours} jam yang lalu`;
        return timestamp.toLocaleDateString('id-ID');
    }

    playNotificationSound() {
        // Simple notification sound
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmcbBTuM0fDTgC4IJXjE8NKSQw');
        audio.volume = 0.3;
        audio.play().catch(e => console.log('Tidak dapat memutar suara notifikasi:', e));
    }

    showToastNotification(message) {
        // Create and show toast notification
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = `
            <i class="fas fa-envelope"></i>
            <span>${message}</span>
        `;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 4000);
    }

    markAllAsRead() {
        this.unreadCount = 0;
        this.updateUI();
    }
}
// Global functions
window.toggleNotifications = function () {
    const dropdown = document.getElementById('notification-dropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

window.markAllAsRead = function () {
    window.notificationManager.markAllAsRead();
}

window.viewSurat = function (noSurat) {
    window.location.href = `/surat/${noSurat}`;
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    window.notificationManager = new NotificationManager();
});
