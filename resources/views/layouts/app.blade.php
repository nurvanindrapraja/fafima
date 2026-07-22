<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#0f172a">
        <link rel="manifest" href="/manifest.json">
        <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
        <link rel="icon" type="image/png" href="{{ asset('icon_fafima_small.png') }}">

        <title>{{ config('app.name', 'FamFinance') }} — {{ $title ?? 'Dashboard' }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        <style>
            body { font-family: 'Inter', sans-serif; background: #0b1022; }

            /* Glassmorphism Cards */
            .card-glass {
                background: rgba(15, 23, 42, 0.60);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
            }

            /* Inputs */
            .input-dark {
                background: rgba(15, 23, 42, 0.80);
                border: 1px solid rgba(100, 116, 139, 0.30);
                border-radius: 0.75rem;
                color: #e2e8f0;
                padding: 0.5rem 0.875rem;
                width: 100%;
                outline: none;
                transition: border-color 0.2s, box-shadow 0.2s;
            }
            .input-dark:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
            .input-dark::placeholder { color: #475569; }
            .input-dark option { background: #1e293b; color: #e2e8f0; }

            /* Primary Button */
            .btn-primary {
                background: linear-gradient(135deg, #1d4ed8, #2563eb);
                color: white;
                box-shadow: 0 4px 15px rgba(37,99,235,0.35);
                transition: all 0.2s;
            }
            .btn-primary:hover { background: linear-gradient(135deg, #2563eb, #3b82f6); transform: translateY(-1px); }
            .btn-primary:active { transform: translateY(0); }
            .btn-primary:disabled { transform: none; opacity: 0.5; }

            /* Sidebar Nav Item */
            .nav-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 1rem; border-radius: 0.75rem; color: #94a3b8; transition: all 0.2s; font-size: 0.875rem; font-weight: 500; }
            .nav-item:hover { background: rgba(59,130,246,0.15); color: #bfdbfe; }
            .nav-item.active { background: rgba(59,130,246,0.25); color: #93c5fd; }

            /* Glow Utility */
            .glow-blue { box-shadow: 0 0 40px rgba(59,130,246,0.15); }
            
            /* Responsive Sidebar Margin */
            @media (min-width: 768px) {
                .desktop-sidebar-margin { margin-left: 16rem; }
            }
        </style>
    </head>
    <body class="antialiased text-slate-200" style="background: linear-gradient(135deg, #0b1022 0%, #0d1b3e 50%, #0b1022 100%); min-height: 100vh;">
        <div x-data="{ sidebarOpen: window.innerWidth >= 768 }" @resize.window="if(window.innerWidth >= 768) sidebarOpen = true; else sidebarOpen = false;">
            
            {{-- Mobile Sidebar (Overlay) --}}
            <div class="md:hidden" x-show="sidebarOpen" style="display: none;">
                {{-- Backdrop --}}
                <div class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm" @click="sidebarOpen = false" x-transition.opacity></div>

                {{-- Sidebar Content --}}
                <aside class="fixed inset-y-0 left-0 z-50 flex flex-col justify-between w-64 border-r border-slate-800/60 card-glass h-full" x-transition:enter="transition-transform ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition-transform ease-in duration-300" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
                    
                    <div class="flex flex-col flex-1 min-h-0">
                        <!-- Brand -->
                        <div class="px-6 py-6 border-b border-slate-800/60 flex justify-between items-center flex-shrink-0">
                            <div class="flex items-center gap-3">
                                <a href="{{ Auth::user()->role === 'admin' ? route('admin.users.index') : route('dashboard') }}" class="flex-shrink-0">
                                    <img src="{{ asset('icon_fafima_small.png') }}" alt="Fafima Logo" class="w-8 h-8 drop-shadow-[0_0_10px_rgba(59,130,246,0.5)]">
                                </a>
                                <div>
                                    <h1 class="text-xl font-extrabold tracking-tight m-0 leading-none pt-0.5">
                                        <span class="text-white">FA</span><span class="text-blue-400">FIMA</span>
                                    </h1>
                                    <p class="text-[10px] text-slate-500 mt-1">Manajemen Keuangan</p>
                                </div>
                            </div>
                            <button @click="sidebarOpen = false" class="text-slate-400 hover:text-white md:hidden">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Family Info (Hidden for Admin) -->
                        @auth
                        @if(Auth::user()->role !== 'admin')
                        <div class="px-4 py-4 border-b border-slate-800/60 flex-shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center text-sm font-bold text-white shadow">
                                    {{ strtoupper(substr(Auth::user()->family?->name ?? 'F', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->family?->name ?? 'Keluarga' }}</p>
                                    <p class="text-xs text-blue-400 capitalize">{{ Auth::user()->role }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Navigation -->
                        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                            @if(Auth::user()->role !== 'admin')
                            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                Dashboard
                            </a>
                            <a href="{{ route('transactions.index') }}" class="nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                Transaksi
                            </a>
                            <a href="{{ route('categories.index') }}" class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                                Kategori
                            </a>
                            <a href="{{ route('targets.index') }}" class="nav-item {{ request()->routeIs('targets.*') ? 'active' : '' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                Target Keuangan
                            </a>
                            <div class="pt-4 mt-4 border-t border-slate-800/60">
                                <a href="{{ url('/family/settings') }}" class="nav-item {{ request()->is('family/settings') ? 'active' : '' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    {{ Auth::user()->role === 'owner' ? 'Pengaturan Keluarga' : 'Anggota Keluarga' }}
                                </a>
                            </div>
                            @endif

                            @if(Auth::user()->role === 'admin')
                            <div class="pt-4 mt-4 border-t border-slate-800/60">
                                <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    Kelola Pengguna
                                </a>
                            </div>
                            @endif
                        </nav>
                    </div>

                    <!-- User Menu -->
                    <div class="px-3 py-4 border-t border-slate-800/60 flex-shrink-0 w-full">
                        <a href="{{ route('profile.edit') }}" class="nav-item hover:bg-slate-800/50 transition-colors">
                            <div class="w-6 h-6 rounded-full {{ Auth::user()->role === 'admin' ? 'bg-purple-600' : 'bg-blue-600' }} flex items-center justify-center text-xs font-bold text-white shadow-sm">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span class="truncate">{{ Auth::user()->name }}</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" onsubmit="confirmLogout(event, this)">
                            @csrf
                            <button type="submit" class="nav-item w-full text-left hover:text-rose-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                    @endauth
                </aside>
            </div>

            {{-- Desktop Sidebar --}}
            <aside class="hidden md:flex flex-col justify-between border-r border-slate-800/60 card-glass transition-all duration-300 ease-in-out fixed inset-y-0 left-0 z-40 h-screen" :style="sidebarOpen ? 'width: 16rem;' : 'width: 0px; opacity: 0;'">
                
                <div class="flex flex-col flex-1 min-h-0 w-full overflow-hidden" style="width: 16rem;">
                    <!-- Brand -->
                    <div class="px-6 py-6 border-b border-slate-800/60 flex-shrink-0">
                        <div class="flex items-center gap-3">
                            <a href="{{ Auth::user()->role === 'admin' ? route('admin.users.index') : route('dashboard') }}" class="flex-shrink-0">
                                <img src="{{ asset('icon_fafima_small.png') }}" alt="Fafima Logo" class="w-8 h-8 drop-shadow-[0_0_10px_rgba(59,130,246,0.5)]">
                            </a>
                            <div>
                                <h1 class="text-xl font-extrabold tracking-tight m-0 leading-none pt-0.5">
                                    <span class="text-white">FA</span><span class="text-blue-400">FIMA</span>
                                </h1>
                                <p class="text-[10px] text-slate-500 mt-1">Manajemen Keuangan</p>
                            </div>
                        </div>
                    </div>

                    <!-- Family Info (Hidden for Admin) -->
                    @auth
                    @if(Auth::user()->role !== 'admin')
                    <div class="px-4 py-4 border-b border-slate-800/60 flex-shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center text-sm font-bold text-white shadow">
                                {{ strtoupper(substr(Auth::user()->family?->name ?? 'F', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->family?->name ?? 'Keluarga' }}</p>
                                <p class="text-xs text-blue-400 capitalize">{{ Auth::user()->role }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Navigation -->
                    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                        @if(Auth::user()->role !== 'admin')
                        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                            Dashboard
                        </a>
                        <a href="{{ route('transactions.index') }}" class="nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            Transaksi
                        </a>
                        <a href="{{ route('categories.index') }}" class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                            Kategori
                        </a>
                        <a href="{{ route('targets.index') }}" class="nav-item {{ request()->routeIs('targets.*') ? 'active' : '' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            Target Keuangan
                        </a>
                        
                        <div class="pt-4 mt-4 border-t border-slate-800/60">
                            <a href="{{ url('/family/settings') }}" class="nav-item {{ request()->is('family/settings') ? 'active' : '' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                {{ Auth::user()->role === 'owner' ? 'Pengaturan Keluarga' : 'Anggota Keluarga' }}
                            </a>
                        </div>
                        @endif

                        @if(Auth::user()->role === 'admin')
                        <div class="pt-4 mt-4 border-t border-slate-800/60">
                            <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                Kelola Pengguna
                            </a>
                        </div>
                        @endif
                    </nav>
                </div>

                <!-- User Menu -->
                <div class="px-3 py-4 border-t border-slate-800/60 flex-shrink-0 w-full" style="width: 16rem;">
                    <a href="{{ route('profile.edit') }}" class="nav-item hover:bg-slate-800/50 transition-colors">
                        <div class="w-6 h-6 rounded-full {{ Auth::user()->role === 'admin' ? 'bg-purple-600' : 'bg-blue-600' }} flex items-center justify-center text-xs font-bold text-white shadow-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <span class="truncate">{{ Auth::user()->name }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" onsubmit="confirmLogout(event, this)">
                        @csrf
                        <button type="submit" class="nav-item w-full text-left hover:text-rose-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                            Keluar
                        </button>
                    </form>
                </div>
                @endauth
            </aside>

            {{-- Main Content Wrapper --}}
            <div class="flex flex-col min-h-screen transition-all duration-300" :class="sidebarOpen ? 'desktop-sidebar-margin' : ''">
                {{-- Unified Header --}}
                <header class="sticky top-0 flex items-center justify-between px-4 py-3 border-b border-slate-800/60 card-glass flex-shrink-0 z-30">
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = !sidebarOpen" class="p-2 -ml-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/50" title="Toggle Menu">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <div class="flex items-center gap-2 md:hidden">
                            <img src="{{ asset('icon_fafima_small.png') }}" alt="Fafima Logo" class="w-6 h-6 drop-shadow-[0_0_8px_rgba(59,130,246,0.5)]">
                            <h1 class="text-lg font-extrabold leading-none pt-0.5"><span class="text-white">FA</span><span class="text-blue-400">FIMA</span></h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-400">
                        {{ Auth::user()->name ?? '' }}
                    </div>
                </header>

                {{-- Scrollable Area --}}
                <main class="flex-1 p-4 sm:p-6 lg:p-8 w-full max-w-7xl mx-auto">
                    @if (isset($header))
                        <header class="mb-6">
                            {{ $header }}
                        </header>
                    @endif
                    {{ $slot }}
                </main>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        @livewireScripts

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('currencyInput', (entangled) => ({
                    raw: entangled,
                    display: '',
                    init() {
                        this.display = this.format(this.raw);
                        this.$watch('raw', value => {
                            this.display = this.format(value);
                        });
                    },
                    format(val) {
                        if (!val && val !== 0) return '';
                        return String(val).replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    },
                    updateValue(e) {
                        let num = e.target.value.replace(/[^0-9]/g, '');
                        this.display = this.format(num);
                        this.raw = num;
                    }
                }));
            });

            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js').then(registration => {
                        console.log('SW registered: ', registration);
                    }).catch(registrationError => {
                        console.log('SW registration failed: ', registrationError);
                    });
                });
            }

            window.confirmLogout = function(e, form) {
                e.preventDefault();
                Swal.fire({
                    title: 'Keluar',
                    text: 'Apakah Anda yakin ingin keluar dari aplikasi?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#475569',
                    confirmButtonText: 'Ya, keluar!',
                    cancelButtonText: 'Batal',
                    background: '#1e293b',
                    color: '#fff',
                    customClass: {
                        popup: 'border border-slate-700/50 rounded-2xl shadow-2xl'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        </script>
    </body>
</html>
