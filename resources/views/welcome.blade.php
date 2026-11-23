<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoardroomConnect Company</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f8fafc',
                            900: '#0f172a',
                            gold: '#d4af37',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full font-sans antialiased text-slate-100 flex flex-col justify-between selection:bg-amber-500 selection:text-white">

    <!-- Header Navigation -->
    <header class="w-full max-w-7xl mx-auto px-6 py-6 flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <span class="text-xl font-semibold tracking-wide text-white">Boardroom<span class="text-amber-400">Connect</span></span>
        </div>

        <nav class="flex items-center space-x-4">
            @if (Route::has('login'))
                @auth
                    <a href="{{ $dashboardUrl }}" class="text-sm font-medium text-slate-300 hover:text-white transition">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-slate-300 hover:text-white transition" data-test="logout-button">
                            Log Out
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-slate-300 hover:text-white px-4 py-2 transition">Sign In</a>
                @endauth
            @endif
        </nav>
    </header>

    <!-- Main Hero Section -->
    <main class="max-w-4xl mx-auto px-6 text-center py-12 my-auto">
        
        <!-- Dynamic Time-Based Greeting Badge -->
        <div class="inline-flex items-center space-x-2 bg-slate-800/80 border border-slate-700/60 rounded-full px-4 py-1.5 mb-8 shadow-inner">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
            <span class="text-xs uppercase tracking-widest text-slate-300 font-semibold">
                @php
                    $hour = date('H');
                    if ($hour < 12) {
                        echo "Good Morning, user";
                    } elseif ($hour < 17) {
                        echo "Good Afternoon, user";
                    } else {
                        echo "Good Evening, user";
                    }
                @endphp
            </span>
        </div>

        <!-- Main Heading -->
        <h1 class="text-4xl sm:text-6xl font-extrabold text-white tracking-tight mb-6 leading-tight">
            Connecting leaders with open <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-200 via-amber-400 to-amber-600">Boardrooms</span>
        </h1>

        <p class="text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto mb-10">
            The premier real-time scheduling platform connecting administrators, secretaries, and corporate members to secure boardrooms instantly and eliminate double-booking conflicts        </p>

        <!-- Dynamic CTAs based on Auth Status -->
        <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4">
            @auth
            <a href="{{ $dashboardUrl }}" class="w-full sm:w-auto inline-flex justify-center items-center bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-8 py-3.5 rounded-xl shadow-lg transition transform hover:-translate-y-0.5">
                    Go to Your Dashboard &rarr;
                </a>
            @else
                <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex justify-center items-center bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-8 py-3.5 rounded-xl shadow-lg transition transform hover:-translate-y-0.5">
                    Sign In to Portal
                </a>
            @endauth
        </div>

        <!-- Platform Feature Highlights -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-20 text-left">
            <div class="bg-slate-800/40 border border-slate-800 p-6 rounded-2xl">
                <div class="text-amber-400 font-bold text-lg mb-2">01. Real-Time Scheduling</div>
                <p class="text-sm text-slate-400">Instantly book and manage boardroom reservations, eliminating double-booking conflicts.</p>
            </div>
            <div class="bg-slate-800/40 border border-slate-800 p-6 rounded-2xl">
                <div class="text-amber-400 font-bold text-lg mb-2">02. Conflict-Free Scheduling</div>
                <p class="text-sm text-slate-400">Dedicated dashboards tailored specifically for Admins, Secretaries, and Staff members.</p>
            </div>
            <div class="bg-slate-800/40 border border-slate-800 p-6 rounded-2xl">
                <div class="text-amber-400 font-bold text-lg mb-2">03. Seamless Reservation Management</div>
                <p class="text-sm text-slate-400">Enterprise-grade security ensuring confidential governance communication and filings.</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full max-w-7xl mx-auto px-6 py-6 text-center text-xs text-slate-500 border-t border-slate-800/80">
        &copy; {{ date('Y') }} BoardroomConnect Platform. All rights reserved.
    </footer>

</body>
</html>
