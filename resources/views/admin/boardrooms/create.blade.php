<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provision Boardroom — BoardroomConnect</title>
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
            <h1 class="text-2xl font-bold text-white">Provision New Boardroom</h1>
            <p class="text-sm text-slate-400 mt-1 mb-6">Register a room and its facilities.</p>
            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/40 text-red-300 px-4 py-3 rounded-xl text-sm mb-6">
                    <ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <form method="POST" action="{{ route('admin.boardrooms.store', ['current_team' => Auth::user()->currentTeam]) }}" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Room Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" required maxlength="100" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="capacity" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Capacity</label>
                        <input id="capacity" name="capacity" type="number" value="{{ old('capacity', 16) }}" min="2" max="500" required class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white">
                    </div>
                    <div>
                        <label for="location" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Location</label>
                        <input id="location" name="location" value="{{ old('location') }}" required maxlength="100" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white">
                    </div>
                </div>
                <div>
                    <label for="amenities" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Amenities</label>
                    <textarea id="amenities" name="amenities" rows="3" maxlength="500" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white">{{ old('amenities') }}</textarea>
                </div>
                <div>
                    <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Status</label>
                    <select id="status" name="status" required class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white">
                        <option value="Available" @selected(old('status', 'Available') === 'Available')>Available</option>
                        <option value="Maintenance" @selected(old('status') === 'Maintenance')>Maintenance</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.dashboard', ['current_team' => Auth::user()->currentTeam]) }}" class="px-4 py-2.5 rounded-xl border border-slate-700 text-sm text-slate-300">Cancel</a>
                    <button class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-5 py-2.5 rounded-xl text-sm">Create Boardroom</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>