<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Reservation — BoardroomConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full font-sans antialiased text-slate-100 flex flex-col selection:bg-amber-500 selection:text-white">
    <header class="w-full bg-slate-950/70 border-b border-slate-800 sticky top-0 z-30 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="{{ route(Auth::user()->role === 'admin' ? 'admin.dashboard' : 'staff.dashboard', ['current_team' => Auth::user()->currentTeam]) }}"
                   class="text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 px-3 py-1.5 rounded-lg transition">
                    &larr; Back to {{ Auth::user()->role === 'admin' ? 'Admin' : 'Staff' }} Dashboard
                </a>
                <span class="text-lg font-semibold tracking-wide text-white">Boardroom<span class="text-amber-400">Connect</span></span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-xs text-slate-400 hidden sm:inline">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold bg-slate-800 hover:bg-red-500/20 hover:text-red-400 text-slate-300 border border-slate-700 px-3.5 py-2 rounded-lg transition">
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-2xl w-full mx-auto px-6 py-10 flex-1">
        <section class="bg-slate-800/60 border border-slate-700/80 rounded-2xl p-8 shadow-2xl">
            <div class="mb-6">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-400">Modify Booking</span>
                <h1 class="text-2xl font-bold text-white mt-1">Edit / Reschedule Reservation</h1>
                <p class="text-sm text-slate-400 mt-1">Update meeting details or move to a different boardroom or time slot. Conflicts are checked automatically.</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 bg-red-500/10 border border-red-500/40 text-red-300 px-4 py-3.5 rounded-xl text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('meetings.update', ['current_team' => Auth::user()->currentTeam, 'meeting' => $meeting->id]) }}" class="space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Meeting Title</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $meeting->title) }}" required
                        class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:border-amber-400">
                </div>

                <div>
                    <label for="boardroom_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Boardroom</label>
                    <select id="boardroom_id" name="boardroom_id" required
                        class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:border-amber-400">
                        @foreach ($boardrooms as $room)
                            <option value="{{ $room->id }}" {{ (int) old('boardroom_id', $meeting->boardroom_id) === $room->id ? 'selected' : '' }}>
                                {{ $room->name }} (Cap: {{ $room->capacity }} &bull; {{ $room->location }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="meeting_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Meeting Date</label>
                    <input type="date" id="meeting_date" name="meeting_date"
                        value="{{ old('meeting_date', \Carbon\Carbon::parse($meeting->meeting_date)->format('Y-m-d')) }}"
                        min="{{ date('Y-m-d') }}" required
                        class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:border-amber-400">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="start_time" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Start Time</label>
                        <input type="time" id="start_time" name="start_time"
                            value="{{ old('start_time', \Carbon\Carbon::parse($meeting->start_time)->format('H:i')) }}" required
                            class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:border-amber-400">
                    </div>
                    <div>
                        <label for="end_time" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">End Time</label>
                        <input type="time" id="end_time" name="end_time"
                            value="{{ old('end_time', \Carbon\Carbon::parse($meeting->end_time)->format('H:i')) }}" required
                            class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:border-amber-400">
                    </div>
                </div>

                <div>
                    <label for="agenda" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Agenda Notes (Optional)</label>
                    <textarea id="agenda" name="agenda" rows="3" placeholder="Key talking points or preparation notes..."
                        class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-400">{{ old('agenda', $meeting->agenda) }}</textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <a href="{{ route(Auth::user()->role === 'admin' ? 'admin.dashboard' : 'staff.dashboard', ['current_team' => Auth::user()->currentTeam]) }}"
                       class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-300 bg-slate-700/60 hover:bg-slate-700 transition">
                        Cancel
                    </a>
                    <button type="submit"
                        class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-6 py-2.5 rounded-xl shadow-lg text-sm transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
