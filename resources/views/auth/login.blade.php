<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — BoardroomConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full font-sans antialiased text-slate-100 flex flex-col justify-center items-center px-6 selection:bg-amber-500 selection:text-white">

    <!-- Brand Logo -->
    <a href="{{ url('/') }}" class="flex items-center space-x-3 mb-8">
        <svg class="w-9 h-9 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
        <span class="text-2xl font-semibold tracking-wide text-white">Boardroom<span class="text-amber-400">Connect</span></span>
    </a>

    <!-- Sign-In Card -->
    <div class="w-full max-w-md bg-slate-800/60 border border-slate-700/80 rounded-2xl p-8 shadow-2xl">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-white">Portal Sign In</h1>
            <p class="text-sm text-slate-400 mt-1">Enter your institutional credentials to access your dashboard</p>
        </div>

        <!-- Validation Error Alert -->
        @if ($errors->any())
            <div class="mb-5 bg-red-500/10 border border-red-500/50 text-red-400 text-sm rounded-xl p-3.5">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf

            <!-- Email Input -->
            <div>
                <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Email Address</label>
                <input 
                    id="email" 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus
                    placeholder="name@company.com"
                    class="w-full px-4 py-2.5 bg-slate-900/90 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition"
                >
            </div>

            <!-- Password Input -->
            <div>
                <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">Password</label>
                <div class="relative">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        placeholder="••••••••"
                        class="w-full px-4 py-2.5 pr-16 bg-slate-900/90 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition"
                    >
                    <button
                        type="button"
                        id="toggle-password-visibility"
                        aria-controls="password"
                        aria-pressed="false"
                        class="absolute inset-y-0 right-0 px-4 text-sm font-medium text-slate-400 hover:text-white transition"
                    >
                        Show
                    </button>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between text-sm">
                <label class="inline-flex items-center text-slate-400 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded bg-slate-900 border-slate-700 text-amber-500 focus:ring-amber-400">
                    <span class="ml-2">Keep me signed in</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-3 rounded-xl shadow-lg transition transform hover:-translate-y-0.5">
                Sign In &rarr;
            </button>
        </form>
    </div>

    <p class="mt-6 text-xs text-slate-500">
        &copy; {{ date('Y') }} BoardroomConnect Platform
    </p>
    <script>
        const passwordInput = document.getElementById('password');
        const passwordVisibilityButton = document.getElementById('toggle-password-visibility');

        passwordVisibilityButton.addEventListener('click', () => {
            const isPasswordVisible = passwordInput.type === 'text';

            passwordInput.type = isPasswordVisible ? 'password' : 'text';
            passwordVisibilityButton.textContent = isPasswordVisible ? 'Show' : 'Hide';
            passwordVisibilityButton.setAttribute('aria-pressed', String(!isPasswordVisible));
        });
    </script>
</body>
</html>