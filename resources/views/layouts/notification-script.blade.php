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

            window.Echo.private(`App.Models.User.${userId}`)
                .notification((notification) => {
                    console.log('New document notification:', notification);
                    this.handleNewNotification(notification);
                });
            // Listen to private channel for current user
            window.Echo.private(`suratmasuk.${userId}`)
                .listen('.surat-masuk', (e) => {
                    console.log('New surat notification:', e);
                    // this.handleNewNotification(e);
                    this.reloadSuratMasukData();
                    this.playNotificationSound();
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

    handleNewNotification(data) {
        // Update count immediately
        this.updateNotificationCount(1);

        // Show browser notification
        this.showBrowserNotification('Surat Masuk Baru', data.message || 'Anda memiliki surat masuk baru');

        // Add bell animation
        this.animateBell();

        // Show toast notification if available
        if (typeof toastr !== 'undefined') {
            toastr.info('Surat masuk baru diterima!', 'Notifikasi');
        }

        // Reload surat masuk table and stats
        this.reloadSuratMasukData();
        // Check and request audio permission if needed
        if ('permissions' in navigator) {
            navigator.permissions.query({
                    name: 'microphone'
                })
                .then(permissionStatus => {
                    if (permissionStatus.state === 'granted') {
                        this.playNotificationSound();
                    } else if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        navigator.mediaDevices.getUserMedia({
                                audio: true
                            })
                            .then(() => this.playNotificationSound())
                            .catch(err => console.log('Audio permission denied:', err));
                    } else {
                        this.playNotificationSound();
                    }
                });
        } else {
            this.playNotificationSound();
        }

    }

    playNotificationSound() {
        const audio = new Audio('/notification.wav');
        audio.play().catch(error => console.log('Error playing notification sound:', error));
    }

    // handleSuratReadNotification(data) {
    //     // Show browser notification
    //     this.showBrowserNotification(
    //         'Surat Telah Dibaca',
    //         `Surat ${data.surat.no_surat} telah dibaca oleh ${data.surat.opened_by}`
    //     );

    //     // Show toast notification if available
    //     if (typeof toastr !== 'undefined') {
    //         toastr.success(`Surat ${data.surat.no_surat} telah dibaca oleh ${data.opened_by}`, 'Surat Dibaca');
    //     }

    //     updateNotificationCount(-1);

    //     alert(`Surat ${data.surat.no_surat} telah dibaca oleh ${data.opened_by}`, 'Surat Dibaca');
    // }

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

    loadUnreadCount() {
        // returns a jQuery promise
        return $.ajax({
            url: "{{ route('notifications.unread-count') }}",
            method: 'GET',
            dataType: 'json'
        }).done((data) => {
            if (data && data.success) {
                this.setNotificationCount(data.unread_count);
            }
        }).fail((jqXHR, textStatus, errorThrown) => {
            console.error('Error loading unread count:', textStatus, errorThrown);
        });
    }

    loadNotifications() {
        if (!this.listElement) return $.Deferred().reject().promise();

        // Show loading
        this.showLoading(true);

        return $.ajax({
            url: "{{ route('notifications.list') }}",
            method: 'GET',
            data: {
                limit: 10
            },
            dataType: 'json'
        }).done((data) => {
            if (data && data.success) {
                this.renderNotifications(data.notifications);
            } else {
                this.listElement.innerHTML = `
                    <div class="text-center py-3 text-muted">
                        <p class="mb-0">Tidak ada notifikasi</p>
                    </div>
                `;
            }
        }).fail((jqXHR, textStatus, errorThrown) => {
            console.error('Error loading notifications:', textStatus, errorThrown);
            if (this.listElement) {
                this.listElement.innerHTML = `
                    <div class="text-center py-3 text-danger">
                        <i class="fas fa-exclamation-triangle mb-2"></i>
                        <p class="mb-0">Error loading notifications</p>
                    </div>
                `;
            }
        }).always(() => {
            this.showLoading(false);
        });
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
            <a href="#" class="dropdown-item ${!notification.is_read ? 'notification-item-unread' : ''}"
               onclick="suratNotificationBell.handleNotificationClick('${notification.id}', event)">
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

            // Optional: redirect to surat detail or management page
            // window.location.href = "{{ route('management-surat.index') }}";
        } catch (error) {
            console.error('Error handling notification click:', error);
        }
    }

    markAsRead(notificationId) {
        const url = "{{ url('/notifications') }}" + '/' + notificationId + '/mark-read';
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        return $.ajax({
            url: url,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token
            },
            dataType: 'json'
        }).done((data) => {
            if (data && data.success) {
                this.updateNotificationCount(-1);
                // Refresh notifications list after short delay
                setTimeout(() => this.loadNotifications(), 300);
            }
        }).fail((jqXHR, textStatus, errorThrown) => {
            console.error('Error marking notification as read:', textStatus, errorThrown);
        });
    }

    markAllAsRead() {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Show loading state
        if (this.markAllReadBtn) {
            this.markAllReadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            this.markAllReadBtn.style.pointerEvents = 'none';
        }

        return $.ajax({
            url: "{{ route('notifications.mark-all-read') }}",
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token
            },
            dataType: 'json'
        }).done((data) => {
            if (data && data.success) {
                this.setNotificationCount(0);
                this.loadNotifications();

                if (typeof toastr !== 'undefined') {
                    toastr.success('Semua notifikasi telah dibaca');
                }
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Error marking notifications as read');
                }
            }
        }).fail((jqXHR, textStatus, errorThrown) => {
            console.error('Error marking all notifications as read:', textStatus, errorThrown);
            if (typeof toastr !== 'undefined') {
                toastr.error('Error marking notifications as read');
            }
        }).always(() => {
            // Reset button state
            if (this.markAllReadBtn) {
                this.markAllReadBtn.innerHTML = 'Mark All As Read';
                this.markAllReadBtn.style.pointerEvents = 'auto';
            }
        });
    }

    setNotificationCount(count) {
        if (!this.countElement) return;

        if (count > 0) {
            this.countElement.textContent = count > 99 ? '99+' : count;
            this.countElement.style.display = 'flex';
            this.countElement.classList.remove('hidden');

            // Remove beep class if exists and add our custom badge
            this.bellElement?.classList.remove('beep');
        } else {
            this.countElement.style.display = 'none';
            this.countElement.classList.add('hidden');

            // Remove beep class
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
