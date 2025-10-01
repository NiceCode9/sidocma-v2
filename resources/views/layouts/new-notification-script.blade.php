<script type="module">
    // Updated Notification Bell JavaScript with Better Styling
    class SuratNotificationBell {
        constructor() {
            this.countElement = document.getElementById('notification-count');
            this.listElement = document.getElementById('notification-list');
            this.markAllReadBtn = document.getElementById('mark-all-read');
            this.bellElement = document.getElementById('notification-bell');
            this.loadingElement = document.getElementById('loading-notifications');

            this.init();
        }

        init() {
            // Load initial data
            this.loadUnreadCount();

            // Setup event listeners
            this.setupEventListeners();

            // Setup Laravel Echo listeners for real-time notifications
            this.setupEchoListeners();
        }

        setupEventListeners() {
            // Mark all as read
            this.markAllReadBtn?.addEventListener('click', (e) => {
                e.preventDefault();
                this.markAllAsRead();
            });

            // Load notifications when dropdown is opened
            this.bellElement?.addEventListener('click', () => {
                this.loadNotifications();
            });
        }

        setupEchoListeners() {
            @auth
            const userId = {{ auth()->id() }};

            // Listen to private channel for document notifications
            window.Echo.private(`App.Models.User.${userId}`)
                .notification((notification) => {
                    console.log('New document notification:', notification);
                    this.handleNewNotification(notification);
                });

            // Listen to private channel for current user
            window.Echo.private(`suratmasuk.${userId}`)
                .listen('.surat-masuk', (e) => {
                    console.log('New surat notification:', e);
                    this.handleNewNotification(e);
                });

            window.Echo.channel(`surat-readed`)
                .listen('.surat-readed', (e) => {
                    console.log('Surat read notification:', e);
                    this.reloadSuratMasukData();
                });

            window.Echo.private(`surat-readed.${userId}`)
                .listen('.surat-readed', (e) => {
                    // handleSuratReadNotification(e);
                    window.tableSuratUnit.ajax.reload(null, false);
                });
        @endauth
    }

    handleNewNotification(notification) {
        // Increment the notification count
        this.updateNotificationCount(1);

        // Show browser notification
        const title = 'Notifikasi Baru';
        const message = notification.message || 'Anda memiliki notifikasi baru.';
        this.showBrowserNotification(title, message);

        // Animate the bell icon
        this.animateBell();

        // Reload surat masuk data if on the relevant page
        this.reloadSuratMasukData();
    }

    async loadNotifications() {
        if (!this.listElement) return;

        // Show loading
        this.showLoading(true);

        try {
            const response = await fetch('/notifications/list');
            const data = await response.json();

            if (data.success) {
                this.renderNotifications(data.notifications);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            this.listElement.innerHTML = `
                <div class="text-center py-3 text-danger">
                    <i class="fas fa-exclamation-triangle mb-2"></i>
                    <p class="mb-0">Error loading notifications</p>
                </div>
            `;
        } finally {
            this.showLoading(false);
        }
    }

    renderNotifications(notifications) {
        if (!this.listElement) return;

        if (notifications.length === 0) {
            this.listElement.innerHTML = `
                <div class="notification-empty">
                    <i class="fas fa-bell-slash text-muted"></i>
                    <p class="mb-0">Tidak ada surat masuk</p>
                </div>
            `;
            return;
        }

        const notificationHtml = notifications.map(notification => `
            <a href="#" class="dropdown-item ${!notification.is_read ? 'notification-item-unread' : ''}">
                <div class="dropdown-item-icon bg-primary text-white">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="dropdown-item-desc">
                    <div class="font-weight-bold">${notification.message}</div>
                    <div class="text-small text-muted mt-1">
                        <span class="badge badge-light mr-1">${notification.no_surat}</span>
                        Dari: ${notification.sender}
                    </div>
                    <div class="time ${!notification.is_read ? 'text-primary font-weight-bold' : 'text-muted'} mt-1">
                        <i class="far fa-clock mr-1"></i>${notification.time_ago}
                    </div>
                </div>
                ${!notification.is_read ? '<div class="dropdown-item-indicator bg-primary"></div>' : ''}
            </a>
        `).join('');

        this.listElement.innerHTML = notificationHtml;
    }

    async handleNotificationClick(notificationId, event) {
        event.preventDefault();

        try {
            await this.markAsRead(notificationId);
        } catch (error) {
            console.error('Error handling notification click:', error);
        }
    }

    reloadSuratMasukData() {
        if (window.location.pathname.includes('management-surat')) {
            const suratMasukTab = document.getElementById('surat-masuk-tab');
            const isOnSuratMasukTab = suratMasukTab && suratMasukTab.classList.contains('active');

            if (isOnSuratMasukTab) {
                if (window.tableSm && typeof window.tableSm.ajax === 'object') {
                    window.tableSm.ajax.reload(null, false);
                }

                if (typeof loadSuratMasukStats === 'function') {
                    loadSuratMasukStats();
                }
            }
        }
    }

    async loadUnreadCount() {
        try {
            const response = await fetch('/notifications/unread-count');
            const data = await response.json();

            if (data.success) {
                this.setNotificationCount(data.unread_count);
            }
        } catch (error) {
            console.error('Error loading unread count:', error);
        }
    }

    setNotificationCount(count) {
        if (!this.countElement) return;

        if (count > 0) {
            this.countElement.textContent = count > 99 ? '99+' : count;
            this.countElement.style.display = 'flex';
            this.countElement.classList.remove('hidden');
            this.bellElement?.classList.remove('beep');
        } else {
            this.countElement.style.display = 'none';
            this.countElement.classList.add('hidden');
            this.bellElement?.classList.remove('beep');
        }
    }

    updateNotificationCount(increment) {
        const currentCount = parseInt(this.countElement?.textContent) || 0;
        const newCount = Math.max(0, currentCount + increment);
        this.setNotificationCount(newCount);
    }

    showBrowserNotification(title, message) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, {
                body: message,
                icon: '/img/avatar/avatar-1.png', // Sesuaikan dengan path icon Anda
                tag: 'surat-notification',
                requireInteraction: false
            });
        }
    }

    animateBell() {
        if (this.bellElement) {
            // Remove existing animation class
            this.bellElement.classList.remove('bell-shake');

            // Add animation class
            this.bellElement.classList.add('bell-shake');

            // Remove animation class after animation completes
            setTimeout(() => {
                this.bellElement.classList.remove('bell-shake');
            }, 500);
        }
    }

    showLoading(show) {
        if (this.loadingElement) {
            this.loadingElement.style.display = show ? 'block' : 'none';
        }

        if (show && this.listElement) {
            this.listElement.innerHTML = `
                <div class="notification-loading">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mb-0 mt-2 text-muted">Memuat notifikasi...</p>
                </div>
            `;
        }
    }
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Initialize notification bell
        window.suratNotificationBell = new SuratNotificationBell();
    });

    // Add additional CSS for dropdown item indicator
    const additionalStyle = document.createElement('style');
    additionalStyle.textContent = `
    .dropdown-item-indicator {
        position: absolute;
        top: 50%;
        right: 10px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        transform: translateY(-50%);
    }

    .dropdown-item {
        position: relative;
        padding-right: 30px;
    }
`;
    document.head.appendChild(additionalStyle);
</script>
