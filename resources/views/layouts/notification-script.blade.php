<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script type="module">
    const countElement = document.getElementById('notification-count');
    const listElement = document.getElementById('notification-list');
    const markAllReadBtn = document.getElementById('mark-all-read');
    const bellElement = document.getElementById('notification-bell');
    const loadingElement = document.getElementById('loading-notifications');

    // Load initial data
    loadUnreadCount();

    // Setup event listeners
    setupEventListeners();

    // Setup Laravel Echo listeners for real-time notifications
    setupEchoListeners();

    loadNotifications();

    function setupEventListeners() {
        markAllReadBtn.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('test');
        })

        bellElement.addEventListener('click', () => {
            loadNotifications();
        });
    }

    function setupEchoListeners() {
        @auth
        window.Echo.private(`suratmasuk.{{ auth()->user()->id }}`)
            .listen('.surat-masuk', (e) => {
                console.log('New surat notification:', e);
                handleNewNotification(e);
            })
    @endauth
    }

    function handleNewNotification(data) {
        // Update count Immediately
        updateNotificationCount(1);

        // Show Browser notification
        showBrowserNotification('Surat Masuk Baru', data.message || 'Anda memiliki surat masuk baru');

        // Add bell notification
        animateBell();

        // Show toast notification if available
        if (typeof toastr !== 'undifined') {
            toastr.info('Surat masuk baru diterima!', 'Notifikasi');
        }
    }

    async function loadUnreadCount() {
        try {
            const response = await fetch('/notifications/unread-count');
            const data = await response.json();
            if (data.success) {
                setNotificationCount(data.unread_count);
            }
        } catch (error) {
            console.error('Error loading unread count:', error);
        }
    }

    async function loadNotifications() {
        if (!listElement) return;

        // Show loading
        showLoading(true);

        try {
            const response = await fetch('/notifications/list?limit=10');
            const data = await response.json();

            if (data.success) {
                renderNotifications(data.notifications);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            listElement.innerHTML = `
                <div class="text-center py-3 text-danger">
                    <i class="fas fa-exclamation-triangle mb-2"></i>
                    <p class="mb-0">Error loading notifications</p>
                </div>
            `;
        } finally {
            showLoading(false);
        }
    }

    function renderNotifications(notifications) {
        if (!listElement) return;
        console.log(notifications)
        if (notifications.length === 0) {
            listElement.innerHTML = `
                <div class="notification-empty">
                    <i class="fas fa-bell-slash text-muted"></i>
                    <p class="mb-0">Tidak ada surat masuk</p>
                </div>
            `;
            return;
        }

        const notificationHtml = notifications.map(notification => `
            <a href="#" class="dropdown-item ${!notification.is_read ? 'notification-item-unread' : ''}"
               onclick="handleNotificationClick(${notification.id}, event)">
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

        listElement.innerHTML = notificationHtml;
    }

    async function handleNotificationClick(notificationId, event) {
        event.preventDefault();

        try {
            await markAsRead(notificationId);

            // Optional: redirect to surat detail or management page
            // window.location.href = '/surat/manage';
        } catch (error) {
            console.error('Error handling notification click:', error);
        }
    }

    async function markAsRead(notificationId) {
        try {
            const response = await fetch(`/notifications/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content')
                }
            });

            const data = await response.json();

            if (data.success) {
                updateNotificationCount(-1);
                // Refresh notifications list after short delay
                setTimeout(() => loadNotifications(), 300);
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async function markAllAsRead() {
        try {
            // Show loading state
            markAllReadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            markAllReadBtn.style.pointerEvents = 'none';

            const response = await fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content')
                }
            });

            const data = await response.json();

            if (data.success) {
                setNotificationCount(0);
                loadNotifications();

                if (typeof toastr !== 'undefined') {
                    toastr.success('Semua notifikasi telah dibaca');
                }
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
            if (typeof toastr !== 'undefined') {
                toastr.error('Error marking notifications as read');
            }
        } finally {
            // Reset button state
            markAllReadBtn.innerHTML = 'Mark All As Read';
            markAllReadBtn.style.pointerEvents = 'auto';
        }
    }

    function setNotificationCount(count) {
        if (!countElement) return;

        if (count > 0) {
            countElement.textContent = count > 99 ? '99+' : count;
            countElement.style.display = 'flex';
            countElement.classList.remove('hidden');

            // Remove beep class if exists and add our custom badge
            bellElement?.classList.remove('beep');
        } else {
            countElement.style.display = 'none';
            countElement.classList.add('hidden');

            // Remove beep class
            bellElement?.classList.remove('beep');
        }
    }

    function updateNotificationCount(increment) {
        const currentCount = parseInt(countElement?.textContent) || 0;
        const newCount = Math.max(0, currentCount + increment);
        setNotificationCount(newCount);
    }

    function showBrowserNotification(title, message) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, {
                body: message,
                icon: '/img/avatar/avatar-1.png', // Sesuaikan dengan path icon Anda
                tag: 'surat-notification',
                requireInteraction: false
            });
        }
    }

    function animateBell() {
        if (bellElement) {
            // Remove existing animation class
            bellElement.classList.remove('bell-shake');

            // Add animation class
            bellElement.classList.add('bell-shake');

            // Remove animation class after animation completes
            setTimeout(() => {
                bellElement.classList.remove('bell-shake');
            }, 500);
        }
    }

    function showLoading(show) {
        if (loadingElement) {
            loadingElement.style.display = show ? 'block' : 'none';
        }

        if (show && listElement) {
            listElement.innerHTML = `
                <div class="notification-loading">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mb-0 mt-2 text-muted">Memuat notifikasi...</p>
                </div>
            `;
        }
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
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
