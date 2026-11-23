<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard — BoardroomConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full font-sans antialiased text-slate-100 flex flex-col selection:bg-amber-500 selection:text-white">

    <!-- Top Navigation Bar -->
    <header class="w-full bg-slate-950/60 border-b border-slate-800 sticky top-0 z-30 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <a href="{{ url('/') }}" class="flex items-center space-x-2.5">
                    <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span class="text-lg font-semibold tracking-wide text-white">Boardroom<span class="text-amber-400">Connect</span></span>
                </a>
                <span class="hidden sm:inline-block text-xs bg-slate-800 text-amber-400 border border-slate-700 px-2.5 py-0.5 rounded-full font-medium">
                    Staff Portal
                </span>
            </div>

            <div class="flex items-center space-x-5">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-medium text-white">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-slate-400">{{ Auth::user()->email }}</div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold bg-slate-800 hover:bg-red-500/20 hover:text-red-400 hover:border-red-500/40 text-slate-300 border border-slate-700 px-3.5 py-2 rounded-lg transition">
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Dashboard Container -->
    <main class="max-w-7xl w-full mx-auto px-6 py-8 flex-1 space-y-8">

        <!-- Flash Notifications (Success or Double-Booking Conflict Alerts) -->
        @if (session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/40 text-emerald-300 px-4 py-3.5 rounded-xl text-sm flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-500/10 border border-red-500/40 text-red-300 px-4 py-3.5 rounded-xl text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Greeting & Quick Summary Stats -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white">Welcome back, {{ Auth::user()->name }}</h1>
                <p class="text-sm text-slate-400 mt-1">Check real-time boardroom availability, reserve a space, and manage your upcoming meetings.</p>
            </div>
            <a href="{{ route('bookings.create', ['current_team' => Auth::user()->currentTeam]) }}"
               class="inline-flex items-center justify-center bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-4 py-2.5 rounded-xl shadow-lg text-sm transition">
                Book a Boardroom
            </a>
        </div>

        <!-- KPI Overview Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="bg-slate-800/50 border border-slate-800 rounded-2xl p-5">
                <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Available Boardrooms</div>
                <div class="text-3xl font-extrabold text-white mt-2">{{ $availableRoomsCount ?? 4 }} <span class="text-sm font-normal text-emerald-400">Open Now</span></div>
            </div>
            <div class="bg-slate-800/50 border border-slate-800 rounded-2xl p-5">
                <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold">My Upcoming Bookings</div>
                <div class="text-3xl font-extrabold text-amber-400 mt-2">{{ isset($myMeetings) ? $myMeetings->count() : 0 }}</div>
            </div>
            <div class="bg-slate-800/50 border border-slate-800 rounded-2xl p-5">
                <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Pending Meeting Invites</div>
                <div class="text-3xl font-extrabold text-white mt-2">{{ $pendingInvitesCount ?? 0 }}</div>
            </div>
        </div>

        <!-- Interactive Daily Boardroom Schedule Grid -->
        <div class="bg-slate-800/50 border border-slate-800 rounded-2xl p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-bold text-white">Daily Availability Grid</h2>
                    <p class="text-xs text-slate-400">Click an <span class="text-emerald-400 font-medium">Open</span> slot to start a reservation for that room and time.</p>
                </div>

                <form method="GET" action="{{ route('staff.dashboard', ['current_team' => Auth::user()->currentTeam]) }}" class="flex items-center space-x-2">
                    <input type="date" name="date" value="{{ $calendarDate ?? date('Y-m-d') }}"
                        class="px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:border-amber-400">
                    <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                        View Date
                    </button>
                </form>
            </div>

            @php
                $hours = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
                $activeDate = $calendarDate ?? date('Y-m-d');
            @endphp

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-700/80 text-slate-400 uppercase tracking-wider">
                            <th class="py-2.5 px-2.5">Time</th>
                            @foreach ($boardrooms ?? [] as $room)
                                <th class="py-2.5 px-2.5">{{ $room->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        @foreach ($hours as $slotStart)
                            @php
                                $slotEnd = date('H:i', strtotime($slotStart.' +1 hour'));
                            @endphp
                            <tr>
                                <td class="py-2 px-2.5 font-mono text-slate-400 whitespace-nowrap">
                                    {{ $slotStart }} – {{ $slotEnd }}
                                </td>

                                @foreach ($boardrooms ?? [] as $room)
                                    @php
                                        $matchingMeeting = ($dailyMeetings ?? collect())->first(function ($meeting) use ($room, $slotStart, $slotEnd) {
                                            $meetingStart = \Carbon\Carbon::parse($meeting->start_time)->format('H:i');
                                            $meetingEnd = \Carbon\Carbon::parse($meeting->end_time)->format('H:i');

                                            return (int) $meeting->boardroom_id === (int) $room->id
                                                && $meetingStart < $slotEnd
                                                && $meetingEnd > $slotStart;
                                        });
                                    @endphp

                                    <td class="py-2 px-2.5">
                                        @if ($room->status !== 'Available')
                                            <span class="inline-block bg-slate-700/50 text-slate-400 px-2 py-1 rounded-md">
                                                Maintenance
                                            </span>
                                        @elseif ($matchingMeeting)
                                            <div class="bg-amber-500/15 border border-amber-500/30 text-amber-300 px-2 py-1 rounded-md truncate max-w-[160px]" title="{{ $matchingMeeting->title }}">
                                                {{ $matchingMeeting->title }}
                                            </div>
                                        @else
                                            <a href="{{ route('bookings.create', ['current_team' => Auth::user()->currentTeam, 'boardroom_id' => $room->id, 'meeting_date' => $activeDate, 'start_time' => $slotStart, 'end_time' => $slotEnd]) }}"
                                                class="inline-block bg-emerald-500/10 hover:bg-emerald-500/25 border border-emerald-500/30 text-emerald-400 px-2 py-1 rounded-md font-medium transition">
                                                Open
                                            </a>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Live Room Status & My Scheduled Meetings -->
        <div class="space-y-6">

                <!-- Live Boardroom Directory -->
                <div class="bg-slate-800/50 border border-slate-800 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-white">Live Boardroom Status</h2>
                        <span class="text-xs text-slate-400">Updated {{ now()->format('H:i') }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @forelse ($boardrooms ?? [] as $room)
                            <div class="bg-slate-900/80 border border-slate-700/70 rounded-xl p-4 flex flex-col justify-between">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-semibold text-white">{{ $room->name }}</h3>
                                        <p class="text-xs text-slate-400">{{ $room->location }} &bull; Seats {{ $room->capacity }}</p>
                                    </div>
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $room->status === 'Available' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30' }}">
                                        {{ $room->status }}
                                    </span>
                                </div>
                                <div class="mt-3 pt-3 border-t border-slate-800 text-xs text-slate-400">
                                    Amenities: <span class="text-slate-300">{{ $room->amenities ?? '4K Display, Video Conferencing, Whiteboard' }}</span>
                            </div>
                        @empty
                            <!-- Sample Fallback Cards until Database Seeded -->
                            <div class="bg-slate-900/80 border border-slate-700/70 rounded-xl p-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-semibold text-white">Executive Suite A</h3>
                                        <p class="text-xs text-slate-400">3rd Floor &bull; Seats 20</p>
                                    </div>
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">Available</span>
                                </div>
                                <div class="mt-3 pt-3 border-t border-slate-800 text-xs text-slate-400">
                                    Amenities: <span class="text-slate-300">Polycom Video Conf, Dual 75" Screens</span>
                                </div>
                            </div>
                            <div class="bg-slate-900/80 border border-slate-700/70 rounded-xl p-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-semibold text-white">Innovation Hub B</h3>
                                        <p class="text-xs text-slate-400">2nd Floor &bull; Seats 12</p>
                                    </div>
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">Available</span>
                                </div>
                                <div class="mt-3 pt-3 border-t border-slate-800 text-xs text-slate-400">
                                    Amenities: <span class="text-slate-300">Smart Whiteboard, Wireless Casting</span>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- My Reservations & Check-In / Release Table -->
                <div class="bg-slate-800/50 border border-slate-800 rounded-2xl p-6">
                    <h2 class="text-lg font-bold text-white mb-4">My Reservations &amp; Governance Packs</h2>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-700/80 text-xs uppercase tracking-wider text-slate-400">
                                    <th class="py-3 px-3">Meeting</th>
                                    <th class="py-3 px-3">Boardroom</th>
                                    <th class="py-3 px-3">Date &amp; Time</th>
                                    <th class="py-3 px-3">Status</th>
                                    <th class="py-3 px-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 text-sm">
                                @forelse ($myMeetings ?? [] as $meeting)
                                    <tr class="hover:bg-slate-800/40 transition">
                                        <td class="py-3.5 px-3">
                                            <a href="{{ route('meetings.show', ['current_team' => Auth::user()->currentTeam, 'meeting' => $meeting->id]) }}" class="font-medium text-amber-400 hover:text-amber-300 underline-offset-2 hover:underline">
                                                {{ $meeting->title }}
                                            </a>
                                        </td>
                                        <td class="py-3.5 px-3 text-slate-300">{{ $meeting->boardroom->name ?? 'Boardroom' }}</td>
                                        <td class="py-3.5 px-3 text-slate-300">
                                            {{ \Carbon\Carbon::parse($meeting->meeting_date)->format('M d, Y') }}<br>
                                            <span class="text-xs text-slate-400">{{ $meeting->start_time }} – {{ $meeting->end_time }}</span>
                                        </td>
                                        <td class="py-3.5 px-3">
                                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                                {{ $meeting->status }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-3 text-right space-x-2">
                                            <a href="{{ route('meetings.edit', ['current_team' => Auth::user()->currentTeam, 'meeting' => $meeting->id]) }}"
                                               class="text-xs text-amber-400 hover:text-amber-300 font-medium mr-2">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('meetings.destroy', ['current_team' => Auth::user()->currentTeam, 'meeting' => $meeting->id]) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('Release this boardroom slot?')" class="text-xs text-red-400 hover:text-red-300 font-medium">
                                                    Release Slot
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-sm text-slate-400">
                                            You have no active boardroom bookings yet. Use the form on the left to reserve a slot.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full max-w-7xl mx-auto px-6 py-5 text-center text-xs text-slate-500 border-t border-slate-800/80">
        &copy; {{ date('Y') }} BoardroomConnect Platform. Staff Workspace.
    </footer>

</body>
</html>