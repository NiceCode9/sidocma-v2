<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }}</title>

    <!-- General CSS Files -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.7.2/css/all.min.css">

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('/stisla/node_modules/prismjs/themes/prism.css') }}">
    <link rel="stylesheet"
        href="{{ asset('/stisla') }}/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet"
        href="{{ asset('/stisla') }}/node_modules/datatables.net-select-bs4/css/select.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('/stisla') }}/node_modules/bootstrap-daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="{{ asset('/stisla') }}/node_modules/select2/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('/stisla/assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('/stisla/assets/css/components.css') }}">

    @vite(['resources/js/app.js'])

    <style>
        /* Custom CSS for Notification Bell */
        .notification-badge {
            position: absolute !important;
            top: 5px !important;
            right: 5px !important;
            background: #dc3545 !important;
            color: white !important;
            font-size: 10px !important;
            font-weight: bold !important;
            min-width: 16px !important;
            height: 16px !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            line-height: 1 !important;
            border: 2px solid #6777ef !important;
            z-index: 10 !important;
            animation: pulse 2s infinite;
        }

        .notification-badge.hidden {
            display: none !important;
        }

        /* Pulse animation for new notifications */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            }

            70% {
                box-shadow: 0 0 0 5px rgba(220, 53, 69, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }

        /* Bell shake animation */
        @keyframes bellShake {

            0%,
            100% {
                transform: rotate(0deg);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: rotate(-10deg);
            }

            20%,
            40%,
            60%,
            80% {
                transform: rotate(10deg);
            }
        }

        .bell-shake {
            animation: bellShake 0.5s ease-in-out;
        }

        /* Notification item styling */
        .notification-item-unread {
            background-color: rgba(103, 119, 239, 0.1) !important;
            border-left: 3px solid #6777ef !important;
        }

        .notification-item-unread .time {
            color: #6777ef !important;
            font-weight: 600 !important;
        }

        /* Dropdown styling improvements */
        .dropdown-list {
            min-width: 320px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .dropdown-list-content {
            max-height: 300px;
            overflow-y: auto;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        /* Empty state styling */
        .notification-empty {
            padding: 2rem 1rem;
            text-align: center;
            color: #8a92b2;
        }

        .notification-empty i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Loading state */
        .notification-loading {
            padding: 1rem;
            text-align: center;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .dropdown-list {
                min-width: 280px;
                right: -50px !important;
            }

            .notification-badge {
                top: 3px !important;
                right: 3px !important;
                min-width: 14px !important;
                height: 14px !important;
                font-size: 9px !important;
            }
        }

        /* Fix for Stisla theme conflicts */
        .navbar .nav-link.position-relative {
            position: relative !important;
        }

        .navbar .notification-toggle {
            padding: 0.5rem 0.75rem !important;
        }

        .navbar .notification-toggle .far.fa-bell {
            font-size: 1.2rem;
        }

        /* Beep effect override */
        .nav-link.beep:after {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body>
    <div id="app">
        <div class="main-wrapper">
            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar">
                <div class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i
                                    class="fas fa-bars"></i></a></li>
                    </ul>
                </div>
                <ul class="navbar-nav navbar-right">


                    {{-- <li class="dropdown dropdown-list-toggle">
                        <a href="#" data-toggle="dropdown" class="nav-link notification-toggle nav-link-lg"
                            id="notification-bell">
                            <i class="far fa-bell"></i>
                            <span class="badge badge-danger notification-count" id="notification-count"
                                style="display: none;">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-list dropdown-menu-right" id="notification-dropdown">
                            <div class="dropdown-header">
                                <span>Notifications</span>
                                <div class="float-right">
                                    <a href="#" id="mark-all-read">Mark All As Read</a>
                                </div>
                            </div>
                            <div class="dropdown-list-content dropdown-list-icons" id="notification-list">
                                <!-- Notifications will be loaded here -->
                                <div class="text-center py-3" id="no-notifications">
                                    <small class="text-muted">No notifications</small>
                                </div>
                            </div>
                            <div class="dropdown-footer text-center">
                                <a href="{{ route('management-surat.index') ?? '#' }}">View All <i
                                        class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </li> --}}

                    <!-- Fixed Notification Bell HTML with Proper Styling -->
                    <li class="dropdown dropdown-list-toggle">
                        <a href="#" data-toggle="dropdown"
                            class="nav-link notification-toggle nav-link-lg position-relative" id="notification-bell">
                            <i class="far fa-bell"></i>
                            <span class="notification-badge" id="notification-count" style="display: none;">0</span>
                        </a>

                        <div class="dropdown-menu dropdown-list dropdown-menu-right">
                            <div class="dropdown-header">
                                Surat Masuk
                                <div class="float-right">
                                    <a href="#" id="mark-all-read" class="text-primary">Mark All As Read</a>
                                </div>
                            </div>

                            <div class="dropdown-list-content dropdown-list-icons" id="notification-list"
                                style="max-height: 300px; overflow-y: auto;">
                                <!-- Notifications will be loaded here -->
                                <div class="text-center py-3" id="loading-notifications">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>

                            <div class="dropdown-footer text-center">
                                <a href="" class="text-primary">View All <i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </li>

                    <li class="dropdown"><a href="#" data-toggle="dropdown"
                            class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <img alt="image" src="{{ asset('/stisla') }}/assets/img/avatar/avatar-1.png"
                                class="rounded-circle mr-1">
                            <div class="d-sm-none d-lg-inline-block">Hi, Ujang Maman</div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div class="dropdown-title">Logged in 5 min ago</div>
                            <a href="features-profile.html" class="dropdown-item has-icon">
                                <i class="far fa-user"></i> Profile
                            </a>
                            <a href="features-activities.html" class="dropdown-item has-icon">
                                <i class="fas fa-bolt"></i> Activities
                            </a>
                            <a href="features-settings.html" class="dropdown-item has-icon">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('logout') }}" class="dropdown-item has-icon text-danger"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                            <form action="{{ route('logout') }}" method="POST" id="logout-form" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                </ul>
            </nav>
            <div class="main-sidebar sidebar-style-2">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand">
                        <a href="index.html">Stisla</a>
                    </div>
                    <div class="sidebar-brand sidebar-brand-sm">
                        <a href="index.html">St</a>
                    </div>
                    <ul class="sidebar-menu">
                        <li class="menu-header">Menu</li>
                        @if (auth()->user()->hasRole('super admin'))
                            <li class="{{ request()->routeIs('dashboard*') ? 'active' : '' }}"><a class="nav-link"
                                    href="{{ route('dashboard') }}"><i class="fas fa-fire"></i>
                                    <span>Dashboard</span></a></li>
                            <li class="menu-header">Data Master</li>
                            {{-- <li class="{{ request()->routeIs('role*') ? 'active' : '' }}"><a class="nav-link"
                                    href="{{ route('role.index') }}"><i class="fas fa-user-tie"></i>
                                    <span>Role</span></a></li> --}}
                            {{-- <li class="{{ request()->routeIs('permission*') ? 'active' : '' }}"><a class="nav-link"
                                    href="{{ route('permission.index') }}"><i class="fas fa-key"></i>
                                    <span>Permission</span></a></li> --}}
                            <li class="{{ request()->routeIs('units*') ? 'active' : '' }}"><a class="nav-link"
                                    href="{{ route('units.index') }}"><i class="fas fa-building"></i>
                                    <span>Unit</span></a></li>
                            <li class="{{ request()->routeIs('document-categories*') ? 'active' : '' }}"><a
                                    class="nav-link" href="{{ route('document-categories.index') }}"><i
                                        class="fas fa-folder"></i>
                                    <span>Document Categories</span></a></li>
                            <li class="{{ request()->routeIs('users*') ? 'active' : '' }}"><a class="nav-link"
                                    href="{{ route('users.index') }}"><i class="fas fa-users"></i>
                                    <span>Users</span></a></li>
                        @endif

                        <li class="menu-header">Manajemen Document</li>
                        <li class="{{ request()->routeIs('folders*') ? 'active' : '' }}"><a class="nav-link"
                                href="{{ route('folders.index') }}"><i class="fas fa-folder-open"></i>
                                <span>{{ auth()->user()->hasRole(['super admin', 'direktur'])? 'Mangement Document': 'Document' }}</span></a>
                        </li>

                        @if (auth()->user()->hasRole(['super admin', 'direktur']))
                            <li class="{{ request()->routeIs('management-surat*') ? 'active' : '' }}"><a
                                    class="nav-link" href="{{ route('management-surat.index') }}"><i
                                        class="fas fa-paper-plane"></i>
                                    <span>Management Surat</span></a></li>
                        @else
                            <li class="{{ request()->routeIs('surat.create') ? 'active' : '' }}"><a class="nav-link"
                                    href="{{ route('kirim-surat.index') }}"><i class="fas fa-envelope"></i>
                                    <span>Kirim Surat</span></a></li>
                        @endif
                    </ul>
                </aside>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                @yield('content')
            </div>

            <!-- Footer -->
            <footer class="main-footer">
                <div class="footer-left">
                    Copyright &copy; 2018 <div class="bullet"></div> Design By <a href="https://nauv.al/">Muhamad
                        Nauval Azhar</a>
                </div>
                <div class="footer-right">
                    2.3.0
                </div>
            </footer>
        </div>
    </div>

    <!-- General JS Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.7.6/jquery.nicescroll.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="{{ asset('/stisla/assets/js/stisla.js') }}"></script>

    <!-- JS Libraies -->
    <script src="{{ asset('/stisla') }}/node_modules/prismjs/prism.js"></script>
    <script src="{{ asset('/stisla') }}/node_modules/datatables/media/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('/stisla') }}/node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="{{ asset('/stisla') }}/node_modules/datatables.net-select-bs4/js/select.bootstrap4.min.js"></script>
    <script src="{{ asset('/stisla') }}/node_modules/bootstrap-daterangepicker/daterangepicker.js"></script>
    <script src="{{ asset('/stisla') }}/node_modules/select2/dist/js/select2.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Template JS File -->
    <script src="{{ asset('/stisla/assets/js/scripts.js') }}"></script>
    <script src="{{ asset('/stisla/assets/js/custom.js') }}"></script>

    <!-- Page Specific JS File -->
    {{-- <script src="{{ asset('/stisla/assets/js/page/bootstrap-modal.js') }}"></script> --}}

    <script>
        @if (session('success'))
            Swal.fire("Sukses!", "{{ session('success') }}", "success");
        @endif

        @if (session('error'))
            Swal.fire("Gagal!", "{{ session('error') }}", "error");
        @endif
    </script>
    </script>

    <script>
        function destroy(url, table = null) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Anda tidak akan dapat mengembalikan ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus saja!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: url,
                        dataType: "json",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    if (table) {
                                        table.ajax.reload();
                                    } else {
                                        location.reload();
                                    }
                                });
                            }
                        },
                        error: function(response) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.responseJSON.message,
                            });
                        }
                    });
                }
            });
        }
    </script>

    {{-- @if (auth()->user()->hasRole('super admin'))
        <script type="module">
            let user = 1;
            console.log(user);
            window.Echo.channel('test-channel')
                .listen('.create', (e) => {
                    console.log('test');
                    console.log(e.model);
                })
        </script>
    @endif --}}

    {{-- <script type="module">
        @auth
        // Listen ke private channel untuk user yang login
        window.Echo.private(`suratmasuk.{{ auth()->user()->id }}`)
            .listen('.surat-masuk', (e) => {
                console.log('Surat baru diterima:', e);
                playNotificationSound();
                // console.log(e)

                // Tampilkan notifikasi atau update UI
                // showNotification('Surat Baru', e.message);

                // Update counter atau refresh data jika diperlukan
                // updateSuratCounter();
            });

        // Optional: Listen ke public channel juga
        window.Echo.channel('suratmasuk')
            .listen('.surat-masuk', (e) => {
                console.log('Public channel - Surat baru:', e);
            });
        @endauth

        console.log(window);

        function playNotificationSound() {
            // Simple notification sound
            const audio = new Audio('/notification.wav');
            audio.volume = 0.3;
            audio.play().catch(e => console.log('Tidak dapat memutar suara notifikasi:', e));
        }
    </script> --}}

    @include('layouts.notification-script')
    @stack('scripts')
</body>

</html>
