<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk - SIDOCMA</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/welcome.css', 'resources/js/welcome.js'])
</head>
<body class="bg-slate-950">

    <!-- ===== CUSTOM CURSOR ===== -->
    <div class="cursor-dot"></div>
    <div class="cursor-ring"></div>
    <div class="cursor-trail"></div>

    <!-- ===== ANIMATED BACKGROUND ===== -->
    <div class="bg-orbs">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>
    <div class="bg-grid"></div>

    <!-- ===== LOGIN PAGE ===== -->
    <div class="relative min-h-screen flex flex-col items-center justify-center px-4 py-10">

        <!-- Top Bar -->
        <div class="w-full max-w-md flex items-center justify-between mb-6">
            <a href="{{ url('/') }}" class="flex items-center space-x-2 px-4 py-2 rounded-xl bg-slate-800/50 backdrop-blur border border-slate-700 text-slate-300 hover:text-teal-400 hover:border-teal-400 transition cursor-hover">
                <i class="fas fa-arrow-left"></i>
                <span class="text-sm font-medium">Beranda</span>
            </a>
            <button id="theme-toggle" class="cursor-hover w-10 h-10 rounded-xl bg-slate-800/50 backdrop-blur border border-slate-700 flex items-center justify-center text-slate-300 hover:text-teal-400 hover:border-teal-400 transition" title="Toggle Theme">
                <i id="theme-icon" class="fas fa-sun"></i>
            </button>
        </div>

        <!-- Login Card -->
        <div class="w-full max-w-md bg-slate-900/70 backdrop-blur-xl rounded-3xl border border-slate-700/50 shadow-2xl overflow-hidden reveal-up">

            <!-- Brand Header -->
            <div class="p-8 pb-6 bg-gradient-to-br from-teal-600 via-teal-700 to-cyan-800">
                <a href="{{ url('/') }}" class="inline-flex items-center space-x-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur flex items-center justify-center group-hover:scale-110 transition-transform">
                        <span class="text-white font-bold text-2xl">S</span>
                    </div>
                    <span class="text-2xl font-bold text-white tracking-wide">SIDOCMA</span>
                </a>
                <h3 class="text-xl font-bold text-white mt-6">Masuk ke Akun Anda</h3>
                <p class="text-teal-100 text-sm mt-1">Sistem Informasi Dokumen & Surat Menyurat</p>
            </div>

            <!-- Form Body -->
            <div class="p-8">

                @if (session('status'))
                <div class="mb-6 p-4 rounded-xl bg-teal-500/10 border border-teal-500/30 text-teal-300 text-sm">
                    {{ session('status') }}
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5" id="loginForm">
                    @csrf

                    <!-- Login -->
                    <div>
                        <label for="login" class="block text-sm font-medium text-slate-300 mb-2">Username / Email</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center justify-center pointer-events-none">
                                <i class="fas fa-user text-slate-500 group-focus-within:text-teal-400 transition"></i>
                            </div>
                            <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus
                                autocomplete="username"
                                class="block w-full pl-11 pr-4 py-3.5 bg-slate-800/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 focus:outline-none transition cursor-hover"
                                placeholder="Masukkan username atau email">
                        </div>
                        @if ($errors->get('login'))
                        <p class="mt-2 text-sm text-red-400 flex items-center space-x-1">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ $errors->first('login') }}</span>
                        </p>
                        @endif
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
                            @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs text-teal-400 hover:text-teal-300 transition cursor-hover">
                                Lupa password?
                            </a>
                            @endif
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center justify-center pointer-events-none">
                                <i class="fas fa-lock text-slate-500 group-focus-within:text-teal-400 transition"></i>
                            </div>
                            <input id="password" type="password" name="password" required
                                autocomplete="current-password"
                                class="block w-full pl-11 pr-12 py-3.5 bg-slate-800/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 focus:outline-none transition cursor-hover"
                                placeholder="Masukkan password">
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center justify-center h-full text-slate-500 hover:text-teal-400 transition cursor-hover">
                                <i id="eyeIcon" class="fas fa-eye"></i>
                            </button>
                        </div>
                        @if ($errors->get('password'))
                        <p class="mt-2 text-sm text-red-400 flex items-center space-x-1">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ $errors->first('password') }}</span>
                        </p>
                        @endif
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer group cursor-hover">
                            <div class="relative">
                                <input id="remember_me" type="checkbox" name="remember"
                                    class="peer sr-only">
                                <div class="w-5 h-5 rounded-md border-2 border-slate-600 peer-checked:bg-teal-500 peer-checked:border-teal-500 transition-all flex items-center justify-center group-hover:border-teal-400">
                                    <i class="fas fa-check text-white text-[10px] opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                                </div>
                            </div>
                            <span class="ml-3 text-sm text-slate-400 group-hover:text-slate-300 transition">Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <div class="pt-4">
                        <button type="submit" id="submitBtn" class="group relative w-full px-8 py-4 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-2xl text-base font-semibold overflow-hidden shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 transition cursor-hover">
                            <span class="relative z-10 flex items-center justify-center space-x-2">
                                <span id="submitText">Masuk Sekarang</span>
                                <i id="submitIcon" class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                                <i id="loadingIcon" class="fas fa-spinner fa-spin hidden"></i>
                            </span>
                            <span class="absolute inset-0 bg-gradient-to-r from-teal-400 to-cyan-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                        </button>
                    </div>
                </form>

                <!-- Footer info -->
                <div class="mt-8 pt-6 border-t border-slate-800 text-center">
                    <p class="text-xs text-slate-500">
                        Akun Anda dibuat dan dikelola oleh <span class="text-teal-400 font-medium">Super Admin</span>.<br>
                        Hubungi administrator jika belum memiliki akses.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer copyright -->
        <p class="mt-8 text-xs text-slate-600 text-center">
            &copy; {{ date('Y') }} SIDOCMA. Sistem Internal Rumah Sakit.
        </p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            const text = document.getElementById('submitText');
            const icon = document.getElementById('submitIcon');
            const loading = document.getElementById('loadingIcon');
            btn.disabled = true;
            btn.classList.add('opacity-70');
            text.textContent = 'Memproses...';
            icon.classList.add('hidden');
            loading.classList.remove('hidden');
        });
    </script>

</body>
</html>