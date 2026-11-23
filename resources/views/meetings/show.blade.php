<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meeting->title }} — Governance Workspace</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-full font-sans antialiased text-slate-100 flex flex-col selection:bg-amber-500 selection:text-white">
    <header class="w-full bg-slate-950/70 border-b border-slate-800 sticky top-0 z-30 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard', ['current_team' => Auth::user()->currentTeam]) : route('staff.dashboard', ['current_team' => Auth::user()->currentTeam]) }}"
                   class="text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 px-3 py-1.5 rounded-lg transition">
                    &larr; Back to Dashboard
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

    <main class="max-w-7xl w-full mx-auto px-6 py-8 flex-1 space-y-8">
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

        <section class="bg-slate-800/60 border border-slate-700/80 rounded-2xl p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center space-x-3 mb-1">
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-500/15 text-amber-400 border border-amber-500/30">
                            {{ $meeting->status }}
                        </span>
                        <span class="text-xs text-slate-400">Organized by {{ $meeting->creator->name }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-white">{{ $meeting->title }}</h1>
                    <p class="text-sm text-slate-400 mt-1">
                        <strong class="text-slate-200">{{ $meeting->boardroom->name }}</strong>
                        ({{ $meeting->boardroom->location }}) &bull;
                        {{ $meeting->meeting_date->format('M d, Y') }} &bull;
                        {{ \Carbon\Carbon::parse($meeting->start_time)->format('g:i A') }} –
                        {{ \Carbon\Carbon::parse($meeting->end_time)->format('g:i A') }}
                    </p>
                </div>
                @if (Auth::user()->role === 'admin' || $meeting->created_by === Auth::id())
                    <a href="{{ route('meetings.edit', ['current_team' => Auth::user()->currentTeam, 'meeting' => $meeting->id]) }}"
                       class="self-start text-sm font-semibold bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg transition">
                        Edit Reservation
                    </a>
                @endif
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1 space-y-6">
                <section class="bg-slate-800/60 border border-slate-700/80 rounded-2xl p-6">
                    <h2 class="text-lg font-bold text-white mb-1">Attendance &amp; RSVP</h2>
                    <p class="text-xs text-slate-400 mb-4">Confirm your availability for this meeting.</p>

                    <form method="POST" action="{{ route('meetings.rsvp', ['current_team' => Auth::user()->currentTeam, 'meeting' => $meeting->id]) }}" class="space-y-3">
                        @csrf

                        @if (Auth::user()->role === 'admin')
                            <div>
                                <label for="rsvp_user_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Attendee</label>
                                <select id="rsvp_user_id" name="user_id" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white">
                                    @foreach ($users as $member)
                                        <option value="{{ $member->id }}" @selected(old('user_id', Auth::id()) == $member->id)>
                                            {{ $member->id === Auth::id() ? 'Myself' : $member->name }} ({{ $member->role }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                            <p class="text-sm text-slate-300">RSVP for {{ Auth::user()->name }}</p>
                        @endif

                        <div>
                            <label for="rsvp_status" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">RSVP Status</label>
                            <select id="rsvp_status" name="status" required class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white">
                                @foreach (['Attending', 'Tentative', 'Declined'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', $myRsvp->status ?? 'Tentative') === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="rsvp_notes" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Note (Optional)</label>
                            <input id="rsvp_notes" type="text" name="notes" value="{{ old('notes', $myRsvp->notes ?? '') }}" maxlength="255" placeholder="e.g., Joining 10 mins late"
                                class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500">
                        </div>

                        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-2.5 rounded-xl text-sm transition">
                            Save RSVP
                        </button>
                    </form>

                    <div class="mt-5 pt-4 border-t border-slate-700/70 space-y-2">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Recorded Responses ({{ $meeting->rsvps->count() }})</div>
                        @forelse ($meeting->rsvps as $rsvp)
                            <div class="flex items-center justify-between text-xs bg-slate-900/70 px-3 py-2 rounded-lg">
                                <div>
                                    <span class="font-medium text-white">{{ $rsvp->user->name }}</span>
                                    @if ($rsvp->notes)
                                        <span class="text-slate-400 block">{{ $rsvp->notes }}</span>
                                    @endif
                                </div>
                                <span class="font-semibold px-2 py-0.5 rounded-full {{ $rsvp->status === 'Attending' ? 'bg-emerald-500/15 text-emerald-400' : ($rsvp->status === 'Declined' ? 'bg-red-500/15 text-red-400' : 'bg-amber-500/15 text-amber-400') }}">
                                    {{ $rsvp->status }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500">No RSVPs recorded yet.</p>
                        @endforelse
                    </div>
                </section>

                <section class="bg-slate-800/60 border border-slate-700/80 rounded-2xl p-6">
                    <h2 class="text-lg font-bold text-white mb-1">Agenda &amp; Board Papers</h2>
                    <p class="text-xs text-slate-400 mb-4">Upload and access meeting files (PDF, DOCX, PPTX, XLSX).</p>

                    <form method="POST" action="{{ route('meetings.documents.store', ['current_team' => Auth::user()->currentTeam, 'meeting' => $meeting->id]) }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <label for="document_title" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Document Title</label>
                            <input id="document_title" type="text" name="title" value="{{ old('title') }}" required maxlength="255" placeholder="e.g., Q4 Financial Pack"
                                class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500">
                        </div>
                        <div>
                            <label for="document" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">File (Max 10MB)</label>
                            <input id="document" type="file" name="document" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx" required
                                class="w-full text-xs text-slate-300 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-700 file:text-amber-400 hover:file:bg-slate-600">
                        </div>
                        <button type="submit" class="w-full bg-slate-700 hover:bg-slate-600 text-white font-semibold py-2.5 rounded-xl text-sm transition">
                            Upload Document
                        </button>
                    </form>

                    <div class="mt-5 pt-4 border-t border-slate-700/70 space-y-2">
                        @forelse ($meeting->documents as $document)
                            <div class="flex items-center justify-between text-xs bg-slate-900/70 px-3 py-2.5 rounded-lg">
                                <div class="truncate pr-2">
                                    <div class="font-medium text-white truncate">{{ $document->title }}</div>
                                    <div class="text-slate-400 truncate">{{ $document->file_name }} · {{ $document->uploader->name }}</div>
                                </div>
                                <a href="{{ route('meetings.documents.download', ['current_team' => Auth::user()->currentTeam, 'meeting' => $meeting->id, 'document' => $document->id]) }}"
                                   class="text-amber-400 hover:text-amber-300 font-semibold shrink-0">
                                    Download
                                </a>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500">No board papers uploaded yet.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="lg:col-span-2">
                <section class="bg-slate-800/60 border border-slate-700/80 rounded-2xl p-6">
                    <h2 class="text-lg font-bold text-white mb-1">Agenda, Official Minutes &amp; Action Items</h2>
                    <p class="text-xs text-slate-400 mb-5">Document key decisions, resolutions, and assigned action owners.</p>

                    @if (Auth::user()->role === 'admin' || $meeting->created_by === Auth::id())
                        <form method="POST" action="{{ route('meetings.minutes.update', ['current_team' => Auth::user()->currentTeam, 'meeting' => $meeting->id]) }}" class="space-y-5">
                            @csrf
                            @method('PATCH')

                            <div>
                                <label for="agenda" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Meeting Agenda</label>
                                <textarea id="agenda" name="agenda" rows="4" maxlength="50000" placeholder="1. Opening & Roll Call&#10;2. Review of Previous Minutes&#10;3. Budget & Resource Approvals"
                                    class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-400">{{ old('agenda', $meeting->agenda) }}</textarea>
                            </div>
                            <div>
                                <label for="minutes" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Recorded Minutes &amp; Resolutions</label>
                                <textarea id="minutes" name="minutes" rows="8" maxlength="50000" placeholder="Record discussions, motions passed, and governance decisions..."
                                    class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-400">{{ old('minutes', $meeting->minutes) }}</textarea>
                            </div>
                            <div>
                                <label for="action_items" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Action Items &amp; Deadlines</label>
                                <textarea id="action_items" name="action_items" rows="4" maxlength="10000" placeholder="- [ ] Prepare Q1 compliance report (Owner: Finance Lead, Due: Friday)"
                                    class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-400">{{ old('action_items', $meeting->action_items) }}</textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-6 py-3 rounded-xl shadow-lg text-sm transition">
                                    Save Governance Records
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="space-y-5">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-300">Meeting Agenda</h3>
                                <p class="mt-2 text-sm text-slate-200 whitespace-pre-line">{{ $meeting->agenda ?: 'No agenda has been added yet.' }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-slate-300">Recorded Minutes &amp; Resolutions</h3>
                                <p class="mt-2 text-sm text-slate-200 whitespace-pre-line">{{ $meeting->minutes ?: 'No minutes have been recorded yet.' }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-slate-300">Action Items &amp; Deadlines</h3>
                                <p class="mt-2 text-sm text-slate-200 whitespace-pre-line">{{ $meeting->action_items ?: 'No action items have been recorded yet.' }}</p>
                            </div>
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </main>
</body>
</html>
