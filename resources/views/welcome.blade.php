<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIDOCMA - Sistem Informasi Dokumen dan Surat Menyurat</title>

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

    <!-- ===== NAVBAR ===== -->
    <nav id="navbar" class="fixed top-0 left-0 right-0 z-40 transition-all duration-500">
        <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="flex items-center justify-between h-20">
                <a href="#hero" class="flex items-center space-x-3 group">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center shadow-lg shadow-teal-500/30 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-white font-bold text-xl">S</span>
                    </div>
                    <span class="text-xl font-bold text-white tracking-wide">SIDOCMA</span>
                </a>

                <div class="hidden md:flex items-center space-x-4">
                    <a href="#fitur" class="text-sm font-medium text-slate-300 hover:text-teal-400 transition cursor-hover magnetic">Fitur</a>

                    <button id="theme-toggle" class="cursor-hover w-10 h-10 rounded-xl bg-slate-800/50 border border-slate-700 flex items-center justify-center text-slate-300 hover:text-teal-400 hover:border-teal-400 transition" title="Toggle Theme">
                        <i id="theme-icon" class="fas fa-sun"></i>
                    </button>

                    @auth
                        <a href="{{ url('/dashboard') }}" class="magnetic-btn group relative px-6 py-3 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-xl text-sm font-semibold overflow-hidden cursor-hover">
                            <span class="relative z-10 flex items-center space-x-2">
                                <span>Dashboard</span>
                                <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                            </span>
                            <span class="absolute inset-0 bg-gradient-to-r from-teal-400 to-cyan-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="magnetic-btn group relative px-6 py-3 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-xl text-sm font-semibold overflow-hidden cursor-hover">
                            <span class="relative z-10 flex items-center space-x-2">
                                <span>Masuk</span>
                                <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                            </span>
                            <span class="absolute inset-0 bg-gradient-to-r from-teal-400 to-cyan-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                        </a>
                    @endauth
                </div>

                <div class="md:hidden flex items-center space-x-2">
                    <button id="theme-toggle-mobile" class="cursor-hover w-10 h-10 rounded-xl bg-slate-800/50 border border-slate-700 flex items-center justify-center text-slate-300" title="Toggle Theme">
                        <i id="theme-icon-mobile" class="fas fa-sun"></i>
                    </button>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-4 py-2 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-lg text-xs font-semibold">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-lg text-xs font-semibold">
                            Masuk
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- ===== HERO SECTION (Fullscreen, single section) ===== -->
    <section id="hero" class="relative min-h-screen flex items-center overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 lg:px-12 py-20 w-full">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <!-- Left Content -->
                <div class="reveal-left">
                    <div class="inline-flex items-center space-x-2 px-4 py-2 rounded-full bg-teal-500/10 border border-teal-400/30 text-teal-300 text-sm font-medium mb-8 cursor-hover">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-400"></span>
                        </span>
                        <span>Sistem Internal Rumah Sakit</span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-7xl font-extrabold leading-[1.1] mb-8 text-white">
                        Sistem Informasi
                        <span class="block text-gradient">Dokumen & Surat</span>
                        <span class="block">Menyurat</span>
                    </h1>

                    <p class="text-lg lg:text-xl text-slate-400 mb-10 max-w-xl leading-relaxed">
                        Kelola seluruh dokumen dan surat menyurat rumah sakit dalam satu platform terpadu. Disposisi multi-unit, pelacakan real-time, dan arsip terstruktur untuk efisiensi organisasi.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 mb-10">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="magnetic-btn group relative px-8 py-4 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-2xl text-base font-semibold overflow-hidden shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 cursor-hover">
                                <span class="relative z-10 flex items-center justify-center space-x-2">
                                    <span>Masuk Dashboard</span>
                                    <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                                </span>
                                <span class="absolute inset-0 bg-gradient-to-r from-teal-400 to-cyan-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="magnetic-btn group relative px-8 py-4 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-2xl text-base font-semibold overflow-hidden shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 cursor-hover">
                                <span class="relative z-10 flex items-center justify-center space-x-2">
                                    <span>Masuk Sekarang</span>
                                    <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                                </span>
                                <span class="absolute inset-0 bg-gradient-to-r from-teal-400 to-cyan-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                            </a>
                        @endauth
                        <button onclick="document.getElementById('fitur-modal').classList.add('open'); document.body.style.overflow='hidden';" class="magnetic-btn group px-8 py-4 bg-slate-800/50 backdrop-blur text-white rounded-2xl text-base font-semibold border border-slate-700 hover:border-teal-400 transition cursor-hover">
                            <span class="flex items-center justify-center space-x-2">
                                <span>Pelajari Fitur</span>
                                <i class="fas fa-chevron-down text-sm group-hover:translate-y-1 transition-transform"></i>
                            </span>
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-slate-400">
                        <div class="flex items-center space-x-2 cursor-hover">
                            <i class="fas fa-shield-halved text-teal-400"></i>
                            <span>Aman & Terenkripsi</span>
                        </div>
                        <div class="flex items-center space-x-2 cursor-hover">
                            <i class="fas fa-bolt text-teal-400"></i>
                            <span>Real-time</span>
                        </div>
                        <div class="flex items-center space-x-2 cursor-hover">
                            <i class="fas fa-layer-group text-teal-400"></i>
                            <span>Multi Role</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Hero Illustration -->
                <div class="reveal-right relative">
                    <div class="hero-illustration-wrap relative">
                        <!-- Glow background -->
                        <div class="hero-glow"></div>

                        <!-- SVG Illustration -->
                        <svg viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg" class="hero-svg relative z-10 w-full h-auto">
                            <defs>
                                <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#14b8a6" />
                                    <stop offset="100%" stop-color="#0d9488" />
                                </linearGradient>
                                <linearGradient id="grad2" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#5eead4" />
                                    <stop offset="100%" stop-color="#14b8a6" />
                                </linearGradient>
                                <filter id="glow" x="-50%" y="-50%" width="200%" height="200%">
                                    <feGaussianBlur stdDeviation="8" result="blur"/>
                                    <feMerge>
                                        <feMergeNode in="blur"/>
                                        <feMergeNode in="SourceGraphic"/>
                                    </feMerge>
                                </filter>
                                <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
                                    <feDropShadow dx="0" dy="10" stdDeviation="15" flood-color="#000000" flood-opacity="0.3"/>
                                </filter>
                            </defs>

                            <!-- Background circles -->
                            <circle cx="250" cy="250" r="220" fill="url(#grad1)" opacity="0.08"/>
                            <circle cx="250" cy="250" r="180" fill="url(#grad1)" opacity="0.12"/>

                            <!-- Floating element 1 (top right) -->
                            <g class="float-1" transform="translate(330, 80)">
                                <circle cx="0" cy="0" r="40" fill="url(#grad1)" filter="url(#shadow)"/>
                                <circle cx="0" cy="0" r="20" fill="white"/>
                                <circle cx="0" cy="0" r="8" fill="#0d9488"/>
                            </g>

                            <!-- Folder -->
                            <g filter="url(#shadow)" transform="translate(0, 0)">
                                <path d="M120 160 L120 360 L380 360 L380 200 L240 200 L220 160 Z" fill="#0f172a" stroke="#14b8a6" stroke-width="2"/>
                                <path d="M120 160 L220 160 L240 200 L380 200" fill="url(#grad2)" opacity="0.4"/>
                            </g>

                            <!-- Document inside -->
                            <g filter="url(#shadow)">
                                <rect x="160" y="220" width="180" height="140" rx="6" fill="#1e293b" stroke="#5eead4" stroke-width="2"/>
                                <rect x="175" y="240" width="100" height="6" rx="3" fill="#14b8a6"/>
                                <rect x="175" y="258" width="150" height="4" rx="2" fill="#475569"/>
                                <rect x="175" y="270" width="130" height="4" rx="2" fill="#475569"/>
                                <rect x="175" y="282" width="140" height="4" rx="2" fill="#475569"/>
                                <rect x="175" y="294" width="110" height="4" rx="2" fill="#475569"/>
                                <rect x="175" y="320" width="70" height="22" rx="4" fill="#14b8a6"/>
                                <text x="210" y="335" font-family="Inter, sans-serif" font-size="11" fill="white" text-anchor="middle" font-weight="700">DISPOSISI</text>
                            </g>

                            <!-- Plus badge -->
                            <g class="float-2" transform="translate(110, 100)">
                                <circle cx="0" cy="0" r="28" fill="white" filter="url(#shadow)"/>
                                <path d="M-10 -2 L-10 2 L-2 2 L-2 10 L2 10 L2 2 L10 2 L10 -2 L2 -2 L2 -10 L-2 -10 L-2 -2 Z" fill="#0d9488"/>
                            </g>

                            <!-- Checkmark badge -->
                            <g class="float-3" transform="translate(420, 380)">
                                <circle cx="0" cy="0" r="32" fill="url(#grad2)" filter="url(#shadow)"/>
                                <path d="M-12 0 L-4 8 L12 -8" stroke="white" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                            </g>

                            <!-- Floating doc 1 -->
                            <g class="float-4" transform="translate(60, 300)">
                                <rect width="40" height="50" rx="4" fill="#1e293b" stroke="#14b8a6" stroke-width="1.5" filter="url(#shadow)"/>
                                <rect x="6" y="10" width="20" height="2" rx="1" fill="#14b8a6"/>
                                <rect x="6" y="16" width="28" height="2" rx="1" fill="#475569"/>
                                <rect x="6" y="22" width="22" height="2" rx="1" fill="#475569"/>
                                <rect x="6" y="28" width="26" height="2" rx="1" fill="#475569"/>
                            </g>

                            <!-- Floating doc 2 -->
                            <g class="float-5" transform="translate(420, 220)">
                                <rect width="40" height="50" rx="4" fill="#1e293b" stroke="#0d9488" stroke-width="1.5" filter="url(#shadow)"/>
                                <rect x="6" y="10" width="20" height="2" rx="1" fill="#0d9488"/>
                                <rect x="6" y="16" width="28" height="2" rx="1" fill="#475569"/>
                                <rect x="6" y="22" width="22" height="2" rx="1" fill="#475569"/>
                                <rect x="6" y="28" width="26" height="2" rx="1" fill="#475569"/>
                            </g>

                            <!-- Connection dashed lines -->
                            <g stroke="#14b8a6" stroke-width="1.5" stroke-dasharray="3,3" opacity="0.4" fill="none">
                                <path d="M150 130 L100 90"/>
                                <path d="M340 100 L410 200"/>
                                <path d="M80 320 L40 380"/>
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FITUR MODAL (slide-in panel) ===== -->
    <div id="fitur-modal" class="modal-overlay">
        <div class="modal-backdrop" onclick="closeFiturModal()"></div>
        <div class="modal-panel">
            <div class="modal-header">
                <h3 class="text-2xl font-bold text-white">Fitur Unggulan</h3>
                <button onclick="closeFiturModal()" class="modal-close cursor-hover">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="feature-row">
                    <div class="feature-icon-lg bg-gradient-to-br from-teal-400 to-teal-600">
                        <i class="fas fa-folder-tree"></i>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-1">Manajemen Dokumen</h4>
                        <p class="text-slate-400 text-sm">Upload, kategorisasi, versioning, dan struktur folder hierarkis dengan pencarian cepat.</p>
                    </div>
                </div>

                <div class="feature-row">
                    <div class="feature-icon-lg bg-gradient-to-br from-cyan-400 to-cyan-600">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-1">Surat Menyurat</h4>
                        <p class="text-slate-400 text-sm">Surat masuk & keluar terintegrasi dengan pelacakan status baca real-time dan lampiran file.</p>
                    </div>
                </div>

                <div class="feature-row">
                    <div class="feature-icon-lg bg-gradient-to-br from-emerald-400 to-emerald-600">
                        <i class="fas fa-share-nodes"></i>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-1">Disposisi Multi-Unit</h4>
                        <p class="text-slate-400 text-sm">Distribusi surat ke banyak unit dengan instruksi spesifik dan tracking paraf otomatis.</p>
                    </div>
                </div>

                <div class="feature-row">
                    <div class="feature-icon-lg bg-gradient-to-br from-violet-400 to-violet-600">
                        <i class="fas fa-users-gear"></i>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-1">Multi-Role & Permission</h4>
                        <p class="text-slate-400 text-sm">Super admin, direktur, dan unit dengan akses granular per folder dan dokumen.</p>
                    </div>
                </div>

                <div class="feature-row">
                    <div class="feature-icon-lg bg-gradient-to-br from-amber-400 to-amber-600">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-1">Notifikasi Real-time</h4>
                        <p class="text-slate-400 text-sm">Broadcast via Laravel Reverb untuk setiap aktivitas penting dalam sistem.</p>
                    </div>
                </div>

                <div class="feature-row">
                    <div class="feature-icon-lg bg-gradient-to-br from-rose-400 to-rose-600">
                        <i class="fas fa-print"></i>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-1">Cetak Lembar Disposisi</h4>
                        <p class="text-slate-400 text-sm">Cetak disposisi format resmi rumah sakit langsung dari sistem.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function closeFiturModal() {
            document.getElementById('fitur-modal').classList.remove('open');
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeFiturModal();
        });
    </script>

</body>
</html>