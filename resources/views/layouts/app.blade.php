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

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('/stisla/assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('/stisla/assets/css/components.css') }}">

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
                            {{-- <li class="menu-header">Data Master</li> --}}
                            {{-- <li class="{{ request()->routeIs('role*') ? 'active' : '' }}"><a class="nav-link"
                                    href="{{ route('role.index') }}"><i class="fas fa-user-tie"></i>
                                    <span>Role</span></a></li> --}}
                            {{-- <li class="{{ request()->routeIs('permission*') ? 'active' : '' }}"><a class="nav-link"
                                    href="{{ route('permission.index') }}"><i class="fas fa-key"></i>
                                    <span>Permission</span></a></li> --}}
                            <li class="{{ request()->routeIs('unit*') ? 'active' : '' }}"><a class="nav-link"
                                    href="{{ route('units.index') }}"><i class="fas fa-building"></i>
                                    <span>Unit</span></a></li>
                            <li class="{{ request()->routeIs('document-categories*') ? 'active' : '' }}"><a
                                    class="nav-link" href="{{ route('document-categories.index') }}"><i
                                        class="fas fa-folder"></i>
                                    <span>Document Categories</span></a></li>
                            {{-- <li class="{{ request()->routeIs('users*') ? 'active' : '' }}"><a class="nav-link"
                                    href="{{ route('users.index') }}"><i class="fas fa-users"></i>
                                    <span>Users</span></a></li> --}}
                        @endif

                        {{-- <li class="menu-header">Manajemen Document</li>
                        <li class="{{ request()->routeIs('folders*') ? 'active' : '' }}"><a class="nav-link"
                                href="{{ route('folders.index') }}"><i class="fas fa-folder-open"></i>
                                <span>Manajemen Documents</span></a></li>
                        <li class="{{ request()->routeIs('arsip-surat*') ? 'active' : '' }}"><a class="nav-link"
                                href="{{ route('arsip-surat.index') }}"><i class="fas fa-envelope"></i>
                                <span>Arsip Surat</span></a></li> --}}

                        {{-- <li class="menu-header">Transaksi</li> --}}

                        {{-- <li class="nav-item dropdown {{ request()->routeIs('tabungan.*') ? 'active' : '' }}">
                            <a href="#" class="nav-link has-dropdown">
                                <i class="fas fa-fire"></i><span>Transaksi</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="">
                                    <a class="nav-link" href="">Menabung</a>
                                </li>
                            </ul>
                        </li> --}}
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

    @stack('scripts')
</body>

</html>
