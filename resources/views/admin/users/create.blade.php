<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User — BoardroomConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-full font-sans antialiased text-slate-100 flex flex-col">
    <header class="w-full bg-slate-950/70 border-b border-slate-800">
        <div class="max-w-3xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ route('admin.dashboard', ['current_team' => Auth::user()->currentTeam]) }}" class="text-sm font-semibold text-amber-400 hover:text-amber-300">&larr; Admin Dashboard</a>
            <span class="text-lg font-semibold tracking-wide text-white">Boardroom<span class="text-amber-400">Connect</span></span>
        </div>
    </header>
    <main class="max-w-3xl w-full mx-auto px-6 py-8 flex-1">
        <section class="bg-slate-800/60 border border-slate-700/80 rounded-2xl p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-white">Register Institutional User</h1>
            <p class="text-sm text-slate-400 mt-1 mb-6">Create an account and assign portal access.</p>
            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/40 text-red-300 px-4 py-3 rounded-xl text-sm mb-6">
                    <ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <form method="POST" action="{{ route('admin.users.store', ['current_team' => Auth::user()->currentTeam]) }}" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Full Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" required maxlength="255" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white">
                </div>
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Institutional Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white">
                </div>
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Temporary Password</label>
                    <input id="password" name="password" type="password" required minlength="8" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white">
                </div>
                <div>
                    <label for="role" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Access Role</label>
                    <select id="role" name="role" required class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white">
                        <option value="staff" @selected(old('role', 'staff') === 'staff')>Staff / Board Member</option>
                        <option value="admin" @selected(old('role') === 'admin')>System Administrator</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.dashboard', ['current_team' => Auth::user()->currentTeam]) }}" class="px-4 py-2.5 rounded-xl border border-slate-700 text-sm text-slate-300">Cancel</a>
                    <button class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-5 py-2.5 rounded-xl text-sm">Create User</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>