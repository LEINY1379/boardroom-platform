<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Command Center — BoardroomConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full font-sans antialiased text-slate-100 flex flex-col selection:bg-amber-500 selection:text-white">

    <!-- Top Navigation Bar -->
    <header class="w-full bg-slate-950/70 border-b border-slate-800 sticky top-0 z-30 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <a href="{{ url('/') }}" class="flex items-center space-x-2.5">
                    <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span class="text-lg font-semibold tracking-wide text-white">Boardroom<span class="text-amber-400">Connect</span></span>
                </a>
                <span class="hidden sm:inline-block text-xs bg-amber-500/15 text-amber-400 border border-amber-500/30 px-2.5 py-0.5 rounded-full font-semibold">
                    Admin Console
                </span>
            </div>

            <div class="flex items-center space-x-5">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-medium text-white">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-amber-400/80">System Administrator</div>
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

        <!-- Flash Notifications -->
        @if (session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/40 text-emerald-300 px-4 py-3.5 rounded-xl text-sm">
                {{ session('success') }}
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

        <!-- Header Title -->
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white">Facility &amp; Governance Oversight</h1>
            <p class="text-sm text-slate-400 mt-1">Provision boardroom assets, monitor utilization metrics, and execute administrative schedule overrides.</p>
            <div class="flex flex-wrap gap-3 mt-4">
                <a href="{{ route('admin.boardrooms.create', ['current_team' => Auth::user()->currentTeam]) }}" class="text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 px-3 py-2 rounded-lg">Boardroom Provision</a>
                <a href="{{ route('admin.users.create', ['current_team' => Auth::user()->currentTeam]) }}" class="text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 px-3 py-2 rounded-lg">Register User</a>
                <a href="{{ route('admin.bookings.create', ['current_team' => Auth::user()->currentTeam]) }}" class="text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 px-3 py-2 rounded-lg">Book a Boardroom</a>
            </div>
        </div>

        <!-- Utilization Analytics KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="bg-slate-800/50 border border-slate-800 rounded-2xl p-5">
                <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Total Boardrooms</div>
                <div class="text-3xl font-extrabold text-white mt-2">{{ isset($boardrooms) ? $boardrooms->count() : 0 }}</div>
            </div>
            <div class="bg-slate-800/50 border border-slate-800 rounded-2xl p-5">
                <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Active Reservations</div>
                <div class="text-3xl font-extrabold text-amber-400 mt-2">{{ $activeBookingsCount ?? 0 }}</div>
            </div>
            <div class="bg-slate-800/50 border border-slate-800 rounded-2xl p-5">
                <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Registered Staff</div>
                <div class="text-3xl font-extrabold text-white mt-2">{{ $totalUsersCount ?? 0 }}</div>
            </div>
            <div class="bg-slate-800/50 border border-slate-800 rounded-2xl p-5">
                <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Utilization Rate</div>
                <div class="text-3xl font-extrabold text-emerald-400 mt-2">{{ $utilizationRate ?? '0%' }}</div>
            </div>
        </div>

        <!-- Boardroom Asset Inventory & Global Schedule Overrides -->
        <div class="space-y-6">

                <!-- Managed Boardroom Assets Table -->
                <div class="bg-slate-800/50 border border-slate-800 rounded-2xl p-6">
                    <h2 class="text-lg font-bold text-white mb-4">Managed Boardroom Assets</h2>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-700/80 text-xs uppercase tracking-wider text-slate-400">
                                    <th class="py-3 px-3">Room &amp; Location</th>
                                    <th class="py-3 px-3">Capacity</th>
                                    <th class="py-3 px-3">Amenities</th>
                                    <th class="py-3 px-3">Status</th>
                                    <th class="py-3 px-3 text-right">Controls</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 text-sm">
                                @forelse ($boardrooms ?? [] as $room)
                                    <tr class="hover:bg-slate-800/40 transition">
                                        <td class="py-3.5 px-3">
                                            <div class="font-semibold text-white">{{ $room->name }}</div>
                                            <div class="text-xs text-slate-400">{{ $room->location }}</div>
                                        </td>
                                        <td class="py-3.5 px-3 text-slate-300">{{ $room->capacity }} seats</td>
                                        <td class="py-3.5 px-3 text-xs text-slate-400 max-w-xs truncate">{{ $room->amenities ?? 'Standard Setup' }}</td>
                                        <td class="py-3.5 px-3">
                                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $room->status === 'Available' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-red-500/10 text-red-400 border border-red-500/30' }}">
                                                {{ $room->status }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-3 text-right space-x-3">
                                            <!-- Toggle Status -->
                                            <form method="POST" action="{{ route('admin.boardrooms.toggle', ['current_team' => Auth::user()->currentTeam, 'boardroom' => $room->id]) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-xs text-amber-400 hover:text-amber-300 font-medium">
                                                    Toggle Status
                                                </button>
                                            </form>

                                            <!-- Delete Room -->
                                            <form method="POST" action="{{ route('admin.boardrooms.destroy', ['current_team' => Auth::user()->currentTeam, 'boardroom' => $room->id]) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('Remove this boardroom and its schedule history?')" class="text-xs text-red-400 hover:text-red-300 font-medium">
                                                    Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-sm text-slate-400">
                                            No boardrooms configured yet. Add your first room using the form on the left.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                </div>

                <!-- Global Reservation Schedule & Priority Overrides -->
                <div class="bg-slate-800/50 border border-slate-800 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-white">Global Reservation Log &amp; Priority Overrides</h2>
                        <span class="text-xs text-slate-400">All Institutional Bookings</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-700/80 text-xs uppercase tracking-wider text-slate-400">
                                    <th class="py-3 px-3">Meeting &amp; Organizer</th>
                                    <th class="py-3 px-3">Boardroom</th>
                                    <th class="py-3 px-3">Date &amp; Slot</th>
                                    <th class="py-3 px-3">Status</th>
                                    <th class="py-3 px-3 text-right">Override</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 text-sm">
                                @forelse ($allMeetings ?? [] as $meeting)
                                    <tr class="hover:bg-slate-800/40 transition">
                                        <td class="py-3.5 px-3">
                                            <div class="font-medium text-white">{{ $meeting->title }}</div>
                                            <div class="text-xs text-slate-400">Booked by: {{ $meeting->creator->name ?? 'Staff Member' }}</div>
                                        </td>
                                        <td class="py-3.5 px-3 text-slate-300">{{ $meeting->boardroom->name ?? 'Boardroom' }}</td>
                                        <td class="py-3.5 px-3 text-slate-300">
                                            {{ \Carbon\Carbon::parse($meeting->meeting_date)->format('M d, Y') }}<br>
                                            <span class="text-xs text-slate-400">{{ $meeting->start_time }} – {{ $meeting->end_time }}</span>
                                        </td>
                                        <td class="py-3.5 px-3">
                                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $meeting->status === 'Cancelled' ? 'bg-red-500/10 text-red-400 border border-red-500/30' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30' }}">
                                                {{ $meeting->status }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-3 text-right">
                                            @if ($meeting->status !== 'Cancelled')
                                                <form method="POST" action="{{ route('admin.meetings.override', ['current_team' => Auth::user()->currentTeam, 'meeting' => $meeting->id]) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" onclick="return confirm('Execute executive override and cancel this booking?')" class="text-xs bg-red-500/15 hover:bg-red-500/30 text-red-400 border border-red-500/40 px-2.5 py-1 rounded-lg font-medium transition">
                                                        Force Cancel
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-slate-500">Overridden</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-sm text-slate-400">
                                            No scheduled meetings found across the organization.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Institutional Directory & Role-Based Access Control (RBAC) -->
                <div class="bg-slate-800/50 border border-slate-800 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-white">Institutional Users &amp; RBAC Roles</h2>
                        <span class="text-xs text-slate-400">{{ $users->count() }} Active Accounts</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-700/80 text-xs uppercase tracking-wider text-slate-400">
                                    <th class="py-3 px-3">User Details</th>
                                    <th class="py-3 px-3">Assigned Role</th>
                                    <th class="py-3 px-3">Joined</th>
                                    <th class="py-3 px-3 text-right">Access Controls</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 text-sm">
                                @foreach ($users as $member)
                                    <tr class="hover:bg-slate-800/40 transition">
                                        <td class="py-3.5 px-3">
                                            <div class="font-semibold text-white">
                                                {{ $member->name }}
                                                @if ($member->id === Auth::id())
                                                    <span class="text-xs text-slate-400 font-normal">(You)</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-slate-400">{{ $member->email }}</div>
                                        </td>
                                        <td class="py-3.5 px-3">
                                            <span class="text-xs uppercase font-semibold px-2.5 py-1 rounded-full {{ $member->role === 'admin' ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'bg-slate-700/60 text-slate-300 border border-slate-600' }}">
                                                {{ $member->role }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-3 text-xs text-slate-400">
                                            {{ $member->created_at?->format('M d, Y') ?? 'N/A' }}
                                        </td>
                                        <td class="py-3.5 px-3 text-right space-x-3">
                                            @if ($member->id !== Auth::id())
                                                <form method="POST" action="{{ route('admin.users.role', ['current_team' => Auth::user()->currentTeam, 'user' => $member->id]) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-xs text-amber-400 hover:text-amber-300 font-medium">
                                                        Switch to {{ $member->role === 'admin' ? 'Staff' : 'Admin' }}
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('admin.users.destroy', ['current_team' => Auth::user()->currentTeam, 'user' => $member->id]) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Revoke portal access for {{ $member->name }}?')" class="text-xs text-red-400 hover:text-red-300 font-medium">
                                                        Revoke
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-slate-500">Protected</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full max-w-7xl mx-auto px-6 py-5 text-center text-xs text-slate-500 border-t border-slate-800/80">
        &copy; {{ date('Y') }} BoardroomConnect Platform. Administrative Command Center.
    </footer>

</body>
</html>