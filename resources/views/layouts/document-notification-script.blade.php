<script type="module">
    // Document Notification Bell System with Laravel Reverb
    class DocumentNotificationBell {
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

            // Setup Laravel Echo (Reverb) listeners
            this.setupEchoListeners();

            // Request browser notification permission
            this.requestNotificationPermission();
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

            // Listen to document channel for real-time updates
            window.Echo.channel('documents')
                .listen('.document.uploaded', (e) => {
                    console.log('Document uploaded:', e);
                    this.reloadDocumentData();
                })
                .listen('.document.updated', (e) => {
                    console.log('Document updated:', e);
                    this.reloadDocumentData();
                })
                .listen('.document.deleted', (e) => {
                    console.log('Document deleted:', e);
                    this.reloadDocumentData();
                });
        @endauth
    }

    handleNewNotification(notification) {
        // Update count immediately
        this.updateNotificationCount(1);

        // Show browser notification
        this.showBrowserNotification(
            'Notifikasi Dokumen',
            notification.message || 'Anda memiliki notifikasi baru'
        );

        // Add bell animation
        this.animateBell();

        // Show toast notification
        this.showToast('info', notification.message || 'Notifikasi baru diterima');

        // Reload document data if on relevant page
        this.reloadDocumentData();
    }

    reloadDocumentData() {
        // Reload document tables if they exist
        if (typeof window.tableDocuments !== 'undefined' && window.tableDocuments.ajax) {
            window.tableDocuments.ajax.reload(null, false);
        }

        // Reload folder tree if exists
        if (typeof refreshFolderTree === 'function') {
            refreshFolderTree();
        }

        // Reload document stats if exists
        if (typeof loadDocumentStats === 'function') {
            loadDocumentStats();
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

    async loadNotifications() {
        if (!this.listElement) return;

        this.showLoading(true);

        try {
            const response = await fetch('/notifications/list?limit=15');
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
                    <div class="notification-empty text-center py-4">
                        <i class="fas fa-bell-slash text-muted fa-2x mb-2"></i>
                        <p class="mb-0 text-muted">Tidak ada notifikasi</p>
                    </div>
                `;
            return;
        }

        const notificationHtml = notifications.map(notification => {
            const icon = this.getNotificationIcon(notification.type);
            const iconColor = this.getNotificationColor(notification.type);

            return `
                    <a href="${notification.action_url}"
                       class="dropdown-item notification-item ${!notification.is_read ? 'notification-item-unread' : ''}"
                       onclick="documentNotificationBell.handleNotificationClick('${notification.id}', event)">
                        <div class="dropdown-item-icon bg-${iconColor} text-white">
                            <i class="${icon}"></i>
                        </div>
                        <div class="dropdown-item-desc">
                            <div class="font-weight-bold">${notification.message}</div>
                            ${notification.document_number ? `
                                <div class="text-small text-muted mt-1">
                                    <span class="badge badge-light">${notification.document_number}</span>
                                </div>
                            ` : ''}
                            ${notification.folder_name ? `
                                <div class="text-small text-muted">
                                    <i class="fas fa-folder mr-1"></i>${notification.folder_name}
                                </div>
                            ` : ''}
                            <div class="time ${!notification.is_read ? 'text-primary font-weight-bold' : 'text-muted'} mt-1">
                                <i class="far fa-clock mr-1"></i>${notification.time_ago}
                            </div>
                        </div>
                        ${!notification.is_read ? '<div class="dropdown-item-indicator bg-primary"></div>' : ''}
                    </a>
                `;
        }).join('');

        this.listElement.innerHTML = notificationHtml;
    }

    getNotificationIcon(type) {
        const icons = {
            'document_uploaded': 'fas fa-cloud-upload-alt',
            'document_shared': 'fas fa-share-alt',
            'document_updated': 'fas fa-edit',
            'document_deleted': 'fas fa-trash',
            'document_permission_granted': 'fas fa-key',
            'document_expiring_soon': 'fas fa-clock',
            'document_approved': 'fas fa-check-circle'
        };
        return icons[type] || 'fas fa-file';
    }

    getNotificationColor(type) {
        const colors = {
            'document_uploaded': 'primary',
            'document_shared': 'info',
            'document_updated': 'warning',
            'document_deleted': 'danger',
            'document_permission_granted': 'success',
            'document_expiring_soon': 'warning',
            'document_approved': 'success'
        };
        return colors[type] || 'secondary';
    }

    async handleNotificationClick(notificationId, event) {
        // Don't prevent default, allow navigation
        try {
            await this.markAsRead(notificationId);
        } catch (error) {
            console.error('Error handling notification click:', error);
        }
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch(`/notifications/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                        .getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.success) {
                this.updateNotificationCount(-1);
                setTimeout(() => this.loadNotifications(), 300);
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            this.markAllReadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            this.markAllReadBtn.style.pointerEvents = 'none';

            const response = await fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                        .getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.success) {
                this.setNotificationCount(0);
                this.loadNotifications();
                this.showToast('success', 'Semua notifikasi telah dibaca');
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
            this.showToast('error', 'Error marking notifications as read');
        } finally {
            this.markAllReadBtn.innerHTML = '<i class="fas fa-check-double"></i> Mark All As Read';
            this.markAllReadBtn.style.pointerEvents = 'auto';
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

    requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    showBrowserNotification(title, message) {
        if ('Notification' in window && Notification.permission === 'granted') {
            const notification = new Notification(title, {
                body: message,
                icon: '/img/avatar/avatar-1.png',
                badge: '/img/avatar/avatar-1.png',
                tag: 'document-notification',
                requireInteraction: false,
                silent: false
            });

            notification.onclick = () => {
                window.focus();
                notification.close();
            };
        }
    }

    animateBell() {
        if (this.bellElement) {
            this.bellElement.classList.remove('bell-shake');

            // Trigger reflow
            void this.bellElement.offsetWidth;

            this.bellElement.classList.add('bell-shake');

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
                    <div class="notification-loading text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mb-0 mt-2 text-muted small">Memuat notifikasi...</p>
                    </div>
                `;
        }
    }

    showToast(type, message) {
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize notification bell
        window.documentNotificationBell = new DocumentNotificationBell();
    });

    // Add CSS for animations and styling
    const notificationStyles = document.createElement('style');
    notificationStyles.textContent = `
        /* Notification Bell Animations */
        @keyframes bellShake {
            0%, 100% { transform: rotate(0deg); }
            10%, 30%, 50%, 70%, 90% { transform: rotate(-10deg); }
            20%, 40%, 60%, 80% { transform: rotate(10deg); }
        }

        .bell-shake {
            animation: bellShake 0.5s ease-in-out;
        }

        /* Notification Count Badge */
        #notification-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            z-index: 10;
        }

        #notification-count.hidden {
            display: none !important;
        }

        /* Notification Dropdown Items */
        .notification-item {
            position: relative;
            padding: 12px 40px 12px 15px;
            border-bottom: 1px solid #f1f1f1;
            transition: all 0.3s ease;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
            transform: translateX(2px);
        }

        .notification-item-unread {
            background-color: #e3f2fd;
        }

        .notification-item-unread:hover {
            background-color: #bbdefb;
        }

        .dropdown-item-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            float: left;
            font-size: 16px;
        }

        .dropdown-item-desc {
            margin-left: 52px;
        }

        .dropdown-item-indicator {
            position: absolute;
            top: 50%;
            right: 15px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            transform: translateY(-50%);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
                transform: translateY(-50%) scale(1);
            }
            50% {
                opacity: 0.5;
                transform: translateY(-50%) scale(1.1);
            }
        }

        /* Notification Empty State */
        .notification-empty {
            padding: 40px 20px;
            text-align: center;
        }

        .notification-empty i {
            font-size: 48px;
            opacity: 0.3;
        }

        /* Notification Loading State */
        .notification-loading {
            padding: 30px 20px;
        }

        /* Mark All Read Button */
        #mark-all-read {
            border-top: 2px solid #e9ecef;
            padding: 12px;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        #mark-all-read:hover {
            background-color: #f8f9fa;
            color: #6777ef;
        }

        /* Notification Dropdown */
        .dropdown-menu-large {
            min-width: 380px;
            max-height: 500px;
            overflow-y: auto;
        }

        /* Custom Scrollbar */
        .dropdown-menu-large::-webkit-scrollbar {
            width: 6px;
        }

        .dropdown-menu-large::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .dropdown-menu-large::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .dropdown-menu-large::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .dropdown-menu-large {
                min-width: 300px;
                max-height: 400px;
            }

            .notification-item {
                padding: 10px 35px 10px 12px;
            }

            .dropdown-item-icon {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }

            .dropdown-item-desc {
                margin-left: 47px;
                font-size: 13px;
            }
        }
    `;
    document.head.appendChild(notificationStyles);
</script>
