<?php

namespace App\Http\Controllers;

use App\Models\Boardroom;
use App\Models\Meeting;
use App\Models\MeetingDocument;
use App\Models\MeetingRsvp;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MeetingController extends Controller
{
    public function staffDashboard(Request $request)
    {
        $boardrooms = Boardroom::orderBy('name')->get();

        $myMeetings = Meeting::with('boardroom')
            ->where('created_by', Auth::id())
            ->orderBy('meeting_date')
            ->orderBy('start_time')
            ->get();

        $calendarDate = $request->input('date', date('Y-m-d'));

        $dailyMeetings = Meeting::with(['boardroom', 'creator'])
            ->whereDate('meeting_date', $calendarDate)
            ->where('status', '!=', 'Cancelled')
            ->orderBy('start_time')
            ->get();

        $availableRoomsCount = $boardrooms->where('status', 'Available')->count();

        return view('staff.dashboard', compact(
            'boardrooms',
            'myMeetings',
            'calendarDate',
            'dailyMeetings',
            'availableRoomsCount'
        ));
    }

    public function create(Request $request)
    {
        $boardrooms = Boardroom::where('status', 'Available')
            ->orderBy('name')
            ->get();

        $bookingDetails = $request->validate([
            'boardroom_id' => ['nullable', 'integer', Rule::in($boardrooms->modelKeys())],
            'meeting_date' => ['nullable', 'date', 'after_or_equal:today'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
        ]);

        return view('staff.bookings.create', [
            'boardrooms' => $boardrooms,
            'bookingDetails' => $bookingDetails,
        ]);
    }

    public function show(string $current_team, Meeting $meeting)
    {
        $team = Team::where('slug', $current_team)->firstOrFail();
        $meeting->load(['boardroom', 'creator', 'documents.uploader', 'rsvps.user']);
        abort_unless($meeting->creator->belongsToTeam($team), 404);

        $users = $team->members()->orderBy('name')->get();
        $myRsvp = $meeting->rsvps->firstWhere('user_id', Auth::id());

        return view('meetings.show', compact('meeting', 'users', 'myRsvp'));
    }

    public function edit(string $current_team, Meeting $meeting)
    {
        $this->authorizeMeetingEdit($current_team, $meeting);

        $boardrooms = Boardroom::where('status', 'Available')
            ->orWhere('id', $meeting->boardroom_id)
            ->orderBy('name')
            ->get();

        return view('meetings.edit', compact('meeting', 'boardrooms'));
    }

    public function update(Request $request, string $current_team, Meeting $meeting)
    {
        $this->authorizeMeetingEdit($current_team, $meeting);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'boardroom_id' => ['required', 'exists:boardrooms,id'],
            'meeting_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'agenda' => ['nullable', 'string', 'max:50000'],
        ]);

        $boardroomIsAvailable = Boardroom::whereKey($validated['boardroom_id'])
            ->where(function ($query) use ($meeting) {
                $query->where('status', 'Available')
                    ->orWhereKey($meeting->boardroom_id);
            })
            ->exists();

        if (! $boardroomIsAvailable) {
            return back()
                ->withInput()
                ->withErrors(['boardroom_id' => 'The selected boardroom is not available.']);
        }

        $updated = DB::transaction(function () use ($validated, $meeting) {
            $conflictExists = Meeting::where('boardroom_id', $validated['boardroom_id'])
                ->whereDate('meeting_date', $validated['meeting_date'])
                ->where('id', '!=', $meeting->id)
                ->where('status', '!=', 'Cancelled')
                ->where(function ($query) use ($validated) {
                    $query->where('start_time', '<', $validated['end_time'])
                        ->where('end_time', '>', $validated['start_time']);
                })
                ->lockForUpdate()
                ->exists();

            if ($conflictExists) {
                return false;
            }

            $meeting->update([
                'title' => $validated['title'],
                'agenda' => $validated['agenda'] ?? null,
                'boardroom_id' => $validated['boardroom_id'],
                'meeting_date' => $validated['meeting_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
            ]);

            return true;
        });

        if (! $updated) {
            return back()
                ->withInput()
                ->withErrors(['conflict' => 'Scheduling conflict: That boardroom is already booked during the new date/time window.']);
        }

        return redirect()
            ->route('meetings.show', ['current_team' => $current_team, 'meeting' => $meeting->id])
            ->with('success', 'Reservation updated and schedule conflict check passed.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'boardroom_id' => ['required', 'exists:boardrooms,id'],
            'meeting_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'agenda' => ['nullable', 'string'],
        ]);

        // Atomic transaction to prevent double-booking race conditions
        $booked = DB::transaction(function () use ($validated) {
            // Lock matching rows for this room & date
            $conflictExists = Meeting::where('boardroom_id', $validated['boardroom_id'])
                ->whereDate('meeting_date', $validated['meeting_date'])
                ->where('status', '!=', 'Cancelled')
                ->where(function ($query) use ($validated) {
                    $query->where('start_time', '<', $validated['end_time'])
                        ->where('end_time', '>', $validated['start_time']);
                })
                ->lockForUpdate()
                ->exists();

            if ($conflictExists) {
                return false;
            }

            $meeting = Meeting::create([
                'title' => $validated['title'],
                'agenda' => $validated['agenda'] ?? null,
                'boardroom_id' => $validated['boardroom_id'],
                'meeting_date' => $validated['meeting_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'created_by' => Auth::id(),
                'status' => 'Scheduled',
            ]);

            // Default organizer RSVP to Attending
            MeetingRsvp::create([
                'meeting_id' => $meeting->id,
                'user_id' => Auth::id(),
                'status' => 'Attending',
            ]);

            return true;
        });

        if (! $booked) {
            return back()
                ->withInput()
                ->withErrors(['conflict' => 'Scheduling conflict: That boardroom is already booked during the selected time window.']);
        }

        return back()->with('success', 'Boardroom reserved successfully! Slot locked.');
    }

    public function updateRsvp(Request $request, string $current_team, Meeting $meeting)
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['required', 'in:Attending,Tentative,Declined'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $team = Team::where('slug', $current_team)->firstOrFail();
        abort_unless($meeting->creator()->whereHas('teams', fn ($query) => $query->whereKey($team->id))->exists(), 404);

        $targetUser = User::whereKey($validated['user_id'] ?? Auth::id())
            ->whereHas('teams', fn ($query) => $query->whereKey($team->id))
            ->firstOrFail();
        abort_if($targetUser->isNot(Auth::user()) && Auth::user()->role !== 'admin', 403);

        MeetingRsvp::updateOrCreate(
            [
                'meeting_id' => $meeting->id,
                'user_id' => $targetUser->id,
            ],
            [
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return back()->with('success', 'RSVP attendance status updated.');
    }

    public function uploadDocument(Request $request, string $current_team, Meeting $meeting)
    {
        $team = Team::where('slug', $current_team)->firstOrFail();
        abort_unless($meeting->creator()->whereHas('teams', fn ($query) => $query->whereKey($team->id))->exists(), 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document' => ['required', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx', 'max:10240'],
        ]);

        $file = $request->file('document');
        $storedPath = $file->store("governance/meetings/{$meeting->id}", 'local');

        MeetingDocument::create([
            'meeting_id' => $meeting->id,
            'uploaded_by' => Auth::id(),
            'title' => $validated['title'],
            'file_path' => $storedPath,
            'file_name' => $file->getClientOriginalName(),
        ]);

        return back()->with('success', 'Board paper / agenda document uploaded.');
    }

    public function downloadDocument(string $current_team, Meeting $meeting, MeetingDocument $document)
    {
        $team = Team::where('slug', $current_team)->firstOrFail();
        abort_unless($meeting->creator()->whereHas('teams', fn ($query) => $query->whereKey($team->id))->exists(), 404);
        abort_unless($document->meeting_id === $meeting->id, 404);

        if (! Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'Document file not found.');
        }

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    public function updateMinutes(Request $request, string $current_team, Meeting $meeting)
    {
        $team = Team::where('slug', $current_team)->firstOrFail();
        abort_unless($meeting->creator()->whereHas('teams', fn ($query) => $query->whereKey($team->id))->exists(), 404);
        abort_if(Auth::user()->role !== 'admin' && $meeting->created_by !== Auth::id(), 403);

        $validated = $request->validate([
            'agenda' => ['nullable', 'string', 'max:50000'],
            'minutes' => ['nullable', 'string', 'max:50000'],
            'action_items' => ['nullable', 'string', 'max:10000'],
        ]);

        $meeting->update($validated);

        return back()->with('success', 'Meeting agenda, minutes, and action items saved.');
    }

    public function destroy($current_team, Meeting $meeting)
    {
        if ($meeting->created_by !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'You can only release your own reservations.');
        }

        $meeting->delete();

        return redirect()
            ->route('staff.dashboard', ['current_team' => Auth::user()->currentTeam])
            ->with('success', 'Boardroom slot released and made available to others.');
    }

    private function authorizeMeetingEdit(string $current_team, Meeting $meeting): void
    {
        $team = Team::where('slug', $current_team)->firstOrFail();
        abort_unless($meeting->creator()->whereHas('teams', fn ($query) => $query->whereKey($team->id))->exists(), 404);
        abort_if($meeting->created_by !== Auth::id() && Auth::user()->role !== 'admin', 403, 'You can only edit your own reservations.');
    }
}
